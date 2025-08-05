<?php

/**
 * AI Analiz Sayfası
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';
require_once 'classes/RateLimiter.php';
require_once 'header.php';
require_once 'navbar_app.php';

// Oturum kontrolü - güvenli authentication
checkLogin();

$user_id = validateUserId($_SESSION['user_id']);

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    // CSRF koruması
    requireCSRFToken();
    
    // Rate limiting kontrolü - MySQL based
    $rateLimiter = RateLimiter::getInstance();
    $userIdentifier = $rateLimiter->getCombinedIdentifier($user_id);
    
    // File upload rate limit check
    $uploadLimit = $rateLimiter->checkLimit('file_upload', $userIdentifier, 'user');
    if (!$uploadLimit['allowed']) {
        $_SESSION['error'] = t('ai.errors.rate_limit_upload', ['time' => date('H:i:s', $uploadLimit['reset_time'])]);
        header("Location: " . SITE_URL . "/ai_analysis.php");
        exit;
    }
    
    // AI analysis rate limit check
    $aiLimit = $rateLimiter->checkLimit('ai_analysis', $userIdentifier, 'user');
    if (!$aiLimit['allowed']) {
        $_SESSION['error'] = t('ai.errors.rate_limit_ai', ['time' => date('H:i:s', $aiLimit['reset_time'])]);
        header("Location: " . SITE_URL . "/ai_analysis.php");
        exit;
    }
    $file = $_FILES['document'];
    $fileName = $file['name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileSize = $file['size'];
    // Güçlü MIME type validasyonu
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fileMime = $finfo->file($file['tmp_name']);
    
    // Backup olarak mime_content_type de kontrol et
    $backupMime = mime_content_type($file['tmp_name']);

    // Dosya boyutu kontrolü (max 10MB)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($fileSize > $maxFileSize) {
        $_SESSION['error'] = t('ai.errors.file_too_large');
        echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
        exit;
    }

    // İzin verilen MIME tipleri - sıkı kontrol
    $allowedMimes = [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'image/jpeg',
        'image/png'
    ];

    // İki farklı MIME type kontrolü de geçmeli
    if (!in_array($fileMime, $allowedMimes) || !in_array($backupMime, $allowedMimes)) {
        $_SESSION['error'] = t('ai.errors.invalid_file_type');
        echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
        exit;
    }

    // Dosya uzantısı kontrolü
    $allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'png', 'jpg', 'jpeg'];
    if (!in_array($fileType, $allowedExtensions)) {
        $_SESSION['error'] = t('ai.errors.invalid_file_extension');
        echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
        exit;
    }

    // Dosya içeriği kontrolü
    $handle = fopen($file['tmp_name'], 'r');
    $header = fread($handle, 8);
    fclose($handle);

    // Zararlı içerik kontrolü
    $maliciousPatterns = [
        '<?php',
        '<?=',
        '<script',
        'eval(',
        'base64_decode(',
        'system(',
        'exec(',
        'shell_exec(',
        'passthru('
    ];

    $fileContent = file_get_contents($file['tmp_name']);
    foreach ($maliciousPatterns as $pattern) {
        if (stripos($fileContent, $pattern) !== false) {
            $_SESSION['error'] = t('ai.errors.malicious_content');
            echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
            exit;
        }
    }

    // Güvenli dosya adı oluştur - hash ile
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $safeFileName = hash('sha256', $fileName . time() . $user_id) . '.' . $fileExtension;

    // Güvenli depolama dizini - web root dışında
    $uploadDir = __DIR__ . '/../secure_uploads/' . $user_id . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Daha güvenli permissions
    }

    $uniqueFileName = uniqid() . '_' . $safeFileName;
    $uploadPath = $uploadDir . $uniqueFileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Google Gemini AI analizi burada yapılacak
        require_once 'vendor/autoload.php';

        // Gemini API anahtarını config'den al
        $apiKey = GEMINI_API_KEY;

        // Dosya içeriğini oku ve AI'ya gönder
        $fileContent = "";
        if ($fileType == "pdf") {
            // PDF okuma işlemi
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($uploadPath);
            $fileContent = $pdf->getText();
        } else {
            // Excel okuma işlemi
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $fileContent = "";
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $fileContent .= $cell->getValue() . "\t";
                }
                $fileContent .= "\n";
            }
        }

        // Gemini API rate limiting kontrolü
        if (!checkGeminiApiLimit($user_id)) {
            $_SESSION['error'] = t('ai.errors.gemini_rate_limit');
            echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
            exit;
        }

        // Gemini AI'ya istek gönder
        $client = new \GuzzleHttp\Client();

        $headers = [
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $apiKey
        ];

        $prompt = <<<EOD
        Bu metin bir finansal döküman (bankadan alınan hesap özeti ya da kredi kartı harcama listesi). Lütfen her satırı analiz et ve aşağıdaki bilgileri çıkar:

        1. Bu bir gelir mi yoksa gider mi?
        2. Tutarı ne kadar?
        3. Para birimi nedir?
        4. Hangi tarihte yapıldı?
        5. Hangi mağazada yapıldı?
        
        Lütfen her bulgu için JSON formatında yanıt ver.
        
        Örnek JSON formatı:
        {
            "type": "income/expense",
            "amount": 100,
            "currency": "USD",
            "description": "Kısa açıklama",
            "date": "2024-01-01",
            "store_name": "Mağaza Adı"
        }
EOD;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt . "\n\n" . $fileContent]
                    ]
                ]
            ]
        ];

        $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
            'headers' => $headers,
            'json' => $data
        ]);

        $result = json_decode($response->getBody(), true);

        // AI sonuçlarını geçici tabloya kaydet
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $aiResults = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);

            foreach ($aiResults as $item) {
                $stmt = $pdo->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, amount, currency, category, suggested_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $fileName,
                    'ekstre',
                    $item['amount'],
                    $item['currency'],
                    $item['type'], // gelir/gider
                    $item['store_name'] . ' - ' . $item['date']
                ]);
            }

            $_SESSION['success'] = t('ai.success.upload_and_analysis');
        } else {
            $_SESSION['error'] = t('ai.errors.analysis_failed');
        }
    } else {
        $_SESSION['error'] = t('ai.errors.upload_failed');
    }
}

// Geçici tablodaki sonuçları getir
$stmt = $pdo->prepare("SELECT * FROM ai_analysis_temp WHERE user_id = ? AND is_approved = 0 ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4" data-translate="ai.analysis_title"><?php echo t('ai.analysis_title'); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Dosya Yükleme Formu -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title" data-translate="ai.upload_file"><?php echo t('ai.upload_file'); ?></h5>
                    <form action="" method="post" enctype="multipart/form-data">
                        <?php echo getCSRFTokenInput(); ?>
                        <div class="mb-3">
                            <label for="document" class="form-label" data-translate="ai.file_select_label"><?php echo t('ai.file_select_label'); ?></label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.xlsx,.xls,.csv,.png,.jpg,.jpeg" required>
                        </div>
                        <button type="submit" class="btn btn-primary" data-translate="ai.upload_and_analyze"><?php echo t('ai.upload_and_analyze'); ?></button>
                    </form>
                </div>
            </div>

            <!-- Analiz Sonuçları -->
            <?php if (!empty($results)): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" data-translate="ai.analysis_results"><?php echo t('ai.analysis_results'); ?></h5>
                        <form action="save_analysis.php" method="post">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th data-translate="ai.table.select"><?php echo t('ai.table.select'); ?></th>
                                            <th data-translate="ai.table.type"><?php echo t('ai.table.type'); ?></th>
                                            <th data-translate="ai.table.description"><?php echo t('ai.table.description'); ?></th>
                                            <th data-translate="ai.table.amount"><?php echo t('ai.table.amount'); ?></th>
                                            <th data-translate="ai.table.suggested_name"><?php echo t('ai.table.suggested_name'); ?></th>
                                            <th data-translate="ai.table.file"><?php echo t('ai.table.file'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="approved[]" value="<?php echo $result['id']; ?>">
                                                </td>
                                                <td><?php echo $result['category'] == 'income' ? t('income.title') : t('payment.title'); ?></td>
                                                <td><?php echo htmlspecialchars($result['description']); ?></td>
                                                <td><?php echo number_format($result['amount'], 2) . ' ' . $result['currency']; ?></td>
                                                <td><?php echo htmlspecialchars($result['suggested_name']); ?></td>
                                                <td><?php echo htmlspecialchars($result['file_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-success" data-translate="ai.save_selected"><?php echo t('ai.save_selected'); ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- JavaScript için dil çevirileri -->
<script>
const translations = {
    ai: {
        analysis_title: '<?php echo t('ai.analysis_title'); ?>',
        upload_file: '<?php echo t('ai.upload_file'); ?>',
        upload_and_analyze: '<?php echo t('ai.upload_and_analyze'); ?>',
        analysis_results: '<?php echo t('ai.analysis_results'); ?>',
        save_selected: '<?php echo t('ai.save_selected'); ?>',
        file_select_label: '<?php echo t('ai.file_select_label'); ?>',
        errors: {
            rate_limit_upload: '<?php echo t('ai.errors.rate_limit_upload'); ?>',
            rate_limit_ai: '<?php echo t('ai.errors.rate_limit_ai'); ?>',
            file_too_large: '<?php echo t('ai.errors.file_too_large'); ?>',
            invalid_file_type: '<?php echo t('ai.errors.invalid_file_type'); ?>',
            invalid_file_extension: '<?php echo t('ai.errors.invalid_file_extension'); ?>',
            malicious_content: '<?php echo t('ai.errors.malicious_content'); ?>',
            gemini_rate_limit: '<?php echo t('ai.errors.gemini_rate_limit'); ?>',
            analysis_failed: '<?php echo t('ai.errors.analysis_failed'); ?>',
            upload_failed: '<?php echo t('ai.errors.upload_failed'); ?>'
        },
        success: {
            upload_and_analysis: '<?php echo t('ai.success.upload_and_analysis'); ?>'
        },
        table: {
            select: '<?php echo t('ai.table.select'); ?>',
            type: '<?php echo t('ai.table.type'); ?>',
            description: '<?php echo t('ai.table.description'); ?>',
            amount: '<?php echo t('ai.table.amount'); ?>',
            suggested_name: '<?php echo t('ai.table.suggested_name'); ?>',
            file: '<?php echo t('ai.table.file'); ?>'
        }
    },
    income: {
        title: '<?php echo t('income.title'); ?>'
    },
    payment: {
        title: '<?php echo t('payment.title'); ?>'
    },
    common: {
        loading: '<?php echo t('loading'); ?>',
        error: '<?php echo t('error'); ?>',
        success: '<?php echo t('success'); ?>'
    }
};
</script>

<!-- Modern Language System -->
<script src="js/language.js"></script>
<script src="js/language-compatibility.js"></script>

<?php require_once 'footer.php'; ?>