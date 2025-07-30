<?php

/**
 * AI Analiz Sonuçlarını Kaydetme
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '" . SITE_URL . "/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approved']) && is_array($_POST['approved'])) {
    $approved_ids = array_map('intval', $_POST['approved']);

    // Onaylanan kayıtları getir
    $placeholders = str_repeat('?,', count($approved_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM ai_analysis_temp WHERE id IN ($placeholders) AND user_id = ?");
    $params = array_merge($approved_ids, [$user_id]);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->beginTransaction();

    try {
        foreach ($items as $item) {
            if ($item['category'] === 'income') {
                // Gelir tablosuna ekle
                $stmt = $pdo->prepare("INSERT INTO income (user_id, name, amount, currency, first_date, frequency, status) 
                                    VALUES (?, ?, ?, ?, CURRENT_DATE, 'once', 'received')");
                $stmt->execute([
                    $user_id,
                    $item['suggested_name'],
                    $item['amount'],
                    $item['currency']
                ]);
            } else {
                // Gider tablosuna ekle
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, name, amount, currency, first_date, frequency, status) 
                                    VALUES (?, ?, ?, ?, CURRENT_DATE, 'once', 'paid')");
                $stmt->execute([
                    $user_id,
                    $item['suggested_name'],
                    $item['amount'],
                    $item['currency']
                ]);
            }

            // Geçici tablodan kaydı işaretleyelim
            $stmt = $pdo->prepare("UPDATE ai_analysis_temp SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$item['id']]);
        }

        $pdo->commit();
        
        // Cache invalidation - AI analiz onayından sonra bu ay cache'ini temizle
        $current_date = date('Y-m-d');
        invalidateSummaryCacheForDate($user_id, $current_date);
        
        // Log AI analysis approval
        if (function_exists('saveLog')) {
            saveLog("AI analysis approved: " . count($items) . " items added", 'info', 'ai_analysis_approve', $user_id);
        }
        
        $_SESSION['success'] = "Seçilen kayıtlar başarıyla eklendi.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Lütfen en az bir kayıt seçin.";
}

// Geri yönlendir
echo "<script>window.location.href = '" . SITE_URL . "/ai_analysis.php';</script>";
exit;
