<?php

/**
 * AI Analiz Sayfası
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';
require_once 'api/rate_limiter.php';
require_once 'header.php';
require_once 'navbar_app.php';

// Oturum kontrolü - güvenli authentication
checkLogin();

$user_id = validateUserId($_SESSION['user_id']);

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    // CSRF koruması
    requireCSRFToken();
    // Rate limiting kontrolü - file upload
    if (!checkFileUploadLimit($user_id)) {
        $_SESSION['error'] = "Çok fazla dosya yükleme denemesi. 5 dakika sonra tekrar deneyin.";
        echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
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
        $_SESSION['error'] = "Dosya boyutu çok büyük. Maksimum 10MB yükleyebilirsiniz.";
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
        $_SESSION['error'] = "Geçersiz dosya türü. Sadece PDF, Excel, CSV, PNG ve JPEG dosyaları yüklenebilir.";
        echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
        exit;
    }

    // Dosya uzantısı kontrolü
    $allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'png', 'jpg', 'jpeg'];
    if (!in_array($fileType, $allowedExtensions)) {
        $_SESSION['error'] = "Geçersiz dosya uzantısı. Sadece PDF, Excel, CSV, PNG ve JPEG dosyaları yüklenebilir.";
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
            $_SESSION['error'] = "Zararlı içerik tespit edildi. Dosya reddedildi.";
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
            $_SESSION['error'] = "AI analizi için çok fazla istek gönderildi. 5 dakika sonra tekrar deneyin.";
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

            $_SESSION['success'] = "Dosya başarıyla yüklendi ve analiz edildi.";
        } else {
            $_SESSION['error'] = "AI analizi sırasında bir hata oluştu.";
        }
    } else {
        $_SESSION['error'] = "Dosya yükleme sırasında bir hata oluştu.";
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
            <h1 class="mb-4">AI Destekli Gelir/Gider Analizi</h1>

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
                    <h5 class="card-title">Dosya Yükle</h5>
                    <form action="" method="post" enctype="multipart/form-data">
                        <?php echo getCSRFTokenInput(); ?>
                        <div class="mb-3">
                            <label for="document" class="form-label">PDF, Excel, CSV, PNG, JPG ve JPEG Dosyası Seçin</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.xlsx,.xls,.csv,.png,.jpg,.jpeg" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Yükle ve Analiz Et</button>
                    </form>
                </div>
            </div>

            <!-- Analiz Sonuçları -->
            <?php if (!empty($results)): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Analiz Sonuçları</h5>
                        <form action="save_analysis.php" method="post">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Seç</th>
                                            <th>Tür</th>
                                            <th>Açıklama</th>
                                            <th>Tutar</th>
                                            <th>Önerilen İsim</th>
                                            <th>Dosya</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="approved[]" value="<?php echo $result['id']; ?>">
                                                </td>
                                                <td><?php echo $result['category'] == 'income' ? 'Gelir' : 'Gider'; ?></td>
                                                <td><?php echo htmlspecialchars($result['description']); ?></td>
                                                <td><?php echo number_format($result['amount'], 2) . ' ' . $result['currency']; ?></td>
                                                <td><?php echo htmlspecialchars($result['suggested_name']); ?></td>
                                                <td><?php echo htmlspecialchars($result['file_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-success">Seçilenleri Kaydet</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>