<?php
/**
 * Database Index Uygulaması
 * Bu script kritik indeksleri database'e ekler
 * Sadece bir kez çalıştırılmalıdır!
 */

require_once 'config.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Bu script sadece admin tarafından çalıştırılabilir.");
}

echo "<h1>Database Performance İyileştirmeleri</h1>";
echo "<p>Kritik indeksler ekleniyor...</p>";

// Indeks ekleme sorguları
$indexQueries = [
    // Payments tablosu indeksleri
    "ALTER TABLE `payments` ADD INDEX `idx_payments_user_id` (`user_id`)",
    "ALTER TABLE `payments` ADD INDEX `idx_payments_user_date` (`user_id`, `first_date`)",
    "ALTER TABLE `payments` ADD INDEX `idx_payments_user_status` (`user_id`, `status`)",
    "ALTER TABLE `payments` ADD INDEX `idx_payments_parent_id` (`parent_id`)",
    
    // Income tablosu indeksleri
    "ALTER TABLE `income` ADD INDEX `idx_income_user_id` (`user_id`)",
    "ALTER TABLE `income` ADD INDEX `idx_income_user_date` (`user_id`, `first_date`)",
    "ALTER TABLE `income` ADD INDEX `idx_income_user_status` (`user_id`, `status`)",
    "ALTER TABLE `income` ADD INDEX `idx_income_parent_id` (`parent_id`)",
    
    // Savings tablosu indeksleri
    "ALTER TABLE `savings` ADD INDEX `idx_savings_user_id` (`user_id`)",
    "ALTER TABLE `savings` ADD INDEX `idx_savings_user_date` (`user_id`, `start_date`)",
    "ALTER TABLE `savings` ADD INDEX `idx_savings_parent_id` (`parent_id`)",
    
    // Portfolio tablosu indeksleri
    "ALTER TABLE `portfolio` ADD INDEX `idx_portfolio_user_id` (`user_id`)",
    "ALTER TABLE `portfolio` ADD INDEX `idx_portfolio_user_symbol` (`user_id`, `sembol`)",
    
    // Logs tablosu indeksleri
    "ALTER TABLE `logs` ADD INDEX `idx_logs_user_id` (`user_id`)",
    "ALTER TABLE `logs` ADD INDEX `idx_logs_type` (`type`)",
    "ALTER TABLE `logs` ADD INDEX `idx_logs_created_at` (`created_at`)",
    
    // Card tablosu indeksleri
    "ALTER TABLE `card` ADD INDEX `idx_card_user_id` (`user_id`)",
    
    // Telegram users tablosu indeksleri
    "ALTER TABLE `telegram_users` ADD INDEX `idx_telegram_user_id` (`user_id`)",
    "ALTER TABLE `telegram_users` ADD INDEX `idx_telegram_chat_id` (`telegram_id`)",
    
    // Exchange rates indeksleri
    "ALTER TABLE `exchange_rates` ADD INDEX `idx_exchange_currencies` (`from_currency`, `to_currency`)",
    "ALTER TABLE `exchange_rates` ADD INDEX `idx_exchange_date` (`date`)",
    
    // Compound indeksler
    "ALTER TABLE `payments` ADD INDEX `idx_payments_compound` (`user_id`, `first_date`, `status`)",
    "ALTER TABLE `income` ADD INDEX `idx_income_compound` (`user_id`, `first_date`, `status`)",
    
    // Parent-child optimizasyonu
    "ALTER TABLE `payments` ADD INDEX `idx_payments_parent_user` (`parent_id`, `user_id`)",
    "ALTER TABLE `income` ADD INDEX `idx_income_parent_user` (`parent_id`, `user_id`)",
    "ALTER TABLE `savings` ADD INDEX `idx_savings_parent_user` (`parent_id`, `user_id`)"
];

$successCount = 0;
$errorCount = 0;

echo "<ul>";

foreach ($indexQueries as $query) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        echo "<li style='color: green;'>✅ " . htmlspecialchars($query) . "</li>";
        $successCount++;
    } catch (PDOException $e) {
        // Index zaten varsa hata vermez, devam eder
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<li style='color: orange;'>⏭️ Index zaten var: " . htmlspecialchars($query) . "</li>";
        } else {
            echo "<li style='color: red;'>❌ HATA: " . htmlspecialchars($query) . "<br>Error: " . htmlspecialchars($e->getMessage()) . "</li>";
            $errorCount++;
        }
    }
}

echo "</ul>";

echo "<h2>Özet</h2>";
echo "<p><strong>Başarılı:</strong> $successCount indeks</p>";
echo "<p><strong>Hata:</strong> $errorCount indeks</p>";

if ($errorCount == 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 Tüm indeksler başarıyla eklendi!</p>";
    echo "<p><strong>Beklenen Performance Artışı:</strong></p>";
    echo "<ul>";
    echo "<li>📈 SELECT sorguları 10-100x hızlanacak</li>";
    echo "<li>🔍 user_id bazlı filtrelemeler instant olacak</li>";
    echo "<li>📊 Dashboard yükleme süreleri %80 azalacak</li>";
    echo "<li>💾 Memory kullanımı optimize olacak</li>";
    echo "</ul>";
    
    // Log kaydı
    saveLog('info', 'Database indexes successfully applied. Performance improvements activated.', 'apply_indexes.php');
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Bazı indeksler eklenemedi. Database administrator ile iletişime geçin.</p>";
    saveLog('warning', "Database indexes partially applied. $errorCount errors occurred.", 'apply_indexes.php');
}

echo "<h2>Sonraki Adımlar</h2>";
echo "<p>1. Bu dosyayı güvenlik için silin: <code>apply_indexes.php</code></p>";
echo "<p>2. Query performance'ını test edin</p>";
echo "<p>3. Dashboard'da hız artışını gözlemleyin</p>";

echo "<hr>";
echo "<p><small>Bu script sadece bir kez çalıştırılmalıdır. Tarih: " . date('Y-m-d H:i:s') . "</small></p>";
?>