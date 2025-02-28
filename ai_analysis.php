<?php
/**
 * AI Analiz Sayfası
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';
require_once 'header.php';
require_once 'navbar.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $fileName = $file['name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Sadece PDF ve Excel dosyalarına izin ver
    if ($fileType != "pdf" && $fileType != "xlsx" && $fileType != "xls") {
        $_SESSION['error'] = "Sadece PDF ve Excel dosyaları yüklenebilir.";
    } else {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uniqueFileName = uniqid() . '_' . $fileName;
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
            
            // Gemini AI'ya istek gönder
            $client = new \Google\Client();
            $client->setApiKey($apiKey);
            
            $prompt = "Bu metin bir finansal döküman. Lütfen her satırı analiz et ve aşağıdaki bilgileri çıkar:
            1. Bu bir gelir mi yoksa gider mi?
            2. Tutarı ne kadar?
            3. Para birimi nedir?
            4. Kısa açıklama nedir?
            5. Önerilen kategori ismi nedir?
            6. Hangi tarihte yapıldı?
            
            Lütfen her bulgu için JSON formatında yanıt ver.";
            
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
                'json' => $data
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            // AI sonuçlarını geçici tabloya kaydet
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $aiResults = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);
                
                foreach ($aiResults as $item) {
                    $stmt = $db->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, description, amount, currency, category, suggested_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $user_id,
                        $fileName,
                        $fileType,
                        $item['description'],
                        $item['amount'],
                        $item['currency'],
                        $item['type'], // gelir/gider
                        $item['category_name']
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
}

// Geçici tablodaki sonuçları getir
$stmt = $db->prepare("SELECT * FROM ai_analysis_temp WHERE user_id = ? AND is_approved = 0 ORDER BY created_at DESC");
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
                        <div class="mb-3">
                            <label for="document" class="form-label">PDF veya Excel Dosyası Seçin</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.xlsx,.xls" required>
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