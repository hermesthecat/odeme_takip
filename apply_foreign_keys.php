<?php
/**
 * Database Foreign Key Constraints Uygulaması
 * Bu script referential integrity sağlar
 * Orphaned records önlenir
 */

require_once 'config.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Bu script sadece admin tarafından çalıştırılabilir.");
}

echo "<h1>Database Integrity İyileştirmeleri</h1>";
echo "<p>Foreign Key Constraints ekleniyor...</p>";

// Önce orphaned records kontrolü yapalım
echo "<h2>Orphaned Records Kontrolü</h2>";

$orphanedQueries = [
    'payments' => "SELECT COUNT(*) as count FROM payments WHERE user_id NOT IN (SELECT id FROM users)",
    'income' => "SELECT COUNT(*) as count FROM income WHERE user_id NOT IN (SELECT id FROM users)", 
    'savings' => "SELECT COUNT(*) as count FROM savings WHERE user_id NOT IN (SELECT id FROM users)",
    'portfolio' => "SELECT COUNT(*) as count FROM portfolio WHERE user_id NOT IN (SELECT id FROM users)",
    'card' => "SELECT COUNT(*) as count FROM card WHERE user_id NOT IN (SELECT id FROM users)",
    'telegram_users' => "SELECT COUNT(*) as count FROM telegram_users WHERE user_id NOT IN (SELECT id FROM users)"
];

$hasOrphans = false;
foreach ($orphanedQueries as $table => $query) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $count = $result['count'];
        
        if ($count > 0) {
            echo "<p style='color: orange;'>⚠️ <strong>$table</strong>: $count orphaned record found</p>";
            $hasOrphans = true;
        } else {
            echo "<p style='color: green;'>✅ <strong>$table</strong>: No orphaned records</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking $table: " . $e->getMessage() . "</p>";
    }
}

if ($hasOrphans) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h3>⚠️ UYARI: Orphaned Records Bulundu</h3>";
    echo "<p>Foreign key constraints eklemeden önce bu orphaned records'ları temizlemek gerekiyor.</p>";
    echo "<p>Bu işlemi manuel olarak yapın veya data loss'u kabul ediyorsanız devam edin.</p>";
    echo "</div>";
}

// Foreign key ekleme sorguları
$foreignKeyQueries = [
    // User references
    "ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `income` ADD CONSTRAINT `fk_income_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `savings` ADD CONSTRAINT `fk_savings_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `portfolio` ADD CONSTRAINT `fk_portfolio_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `card` ADD CONSTRAINT `fk_card_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `logs` ADD CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE",
    "ALTER TABLE `telegram_users` ADD CONSTRAINT `fk_telegram_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    
    // Parent-child relationships
    "ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_parent` FOREIGN KEY (`parent_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `income` ADD CONSTRAINT `fk_income_parent` FOREIGN KEY (`parent_id`) REFERENCES `income`(`id`) ON DELETE CASCADE ON UPDATE CASCADE", 
    "ALTER TABLE `savings` ADD CONSTRAINT `fk_savings_parent` FOREIGN KEY (`parent_id`) REFERENCES `savings`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    
    // Card relationships
    "ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_card` FOREIGN KEY (`card_id`) REFERENCES `card`(`id`) ON DELETE SET NULL ON UPDATE CASCADE",
    
    // Portfolio relationships  
    "ALTER TABLE `portfolio` ADD CONSTRAINT `fk_portfolio_referans` FOREIGN KEY (`referans_alis_id`) REFERENCES `portfolio`(`id`) ON DELETE SET NULL ON UPDATE CASCADE"
];

echo "<h2>Foreign Key Constraints Uygulanıyor</h2>";
echo "<ul>";

$successCount = 0;
$errorCount = 0;

foreach ($foreignKeyQueries as $query) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        echo "<li style='color: green;'>✅ " . htmlspecialchars($query) . "</li>";
        $successCount++;
    } catch (PDOException $e) {
        // Foreign key zaten varsa veya orphaned record varsa
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<li style='color: orange;'>⏭️ Foreign key zaten var: " . htmlspecialchars($query) . "</li>";
        } elseif (strpos($e->getMessage(), 'foreign key constraint fails') !== false || 
                  strpos($e->getMessage(), 'Cannot add') !== false) {
            echo "<li style='color: red;'>❌ ORPHANED RECORD: " . htmlspecialchars($query) . "<br><small>Error: " . htmlspecialchars($e->getMessage()) . "</small></li>";
            echo "<li style='padding-left: 20px; color: red;'><small>➡️ Bu tabloda orphaned record var, önce temizlemek gerekiyor</small></li>";
            $errorCount++;
        } else {
            echo "<li style='color: red;'>❌ HATA: " . htmlspecialchars($query) . "<br>Error: " . htmlspecialchars($e->getMessage()) . "</li>";
            $errorCount++;
        }
    }
}

echo "</ul>";

echo "<h2>Özet</h2>";
echo "<p><strong>Başarılı:</strong> $successCount foreign key</p>";
echo "<p><strong>Hata:</strong> $errorCount foreign key</p>";

if ($errorCount == 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 Tüm foreign key constraints başarıyla eklendi!</p>";
    echo "<p><strong>Sağlanan Faydalar:</strong></p>";
    echo "<ul>";
    echo "<li>🔒 <strong>Referential Integrity:</strong> Orphaned records artık impossible</li>";
    echo "<li>🗑️ <strong>Cascade Deletes:</strong> User silindiğinde tüm data otomatik temizlenir</li>";
    echo "<li>✅ <strong>Data Validation:</strong> Invalid references database seviyesinde reddedilir</li>";
    echo "<li>⚡ <strong>Performance:</strong> Foreign key indexes otomatik eklenir</li>";
    echo "</ul>";
    
    // Log kaydı
    saveLog('Database foreign key constraints successfully applied. Referential integrity activated.', 'info', 'apply_foreign_keys.php', $_SESSION['user_id']);
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Bazı foreign key constraints eklenemedi.</p>";
    echo "<p><strong>Çözüm Önerileri:</strong></p>";
    echo "<ul>";
    echo "<li>Orphaned records'ları manuel olarak temizleyin</li>";
    echo "<li>Data integrity kontrolü yapın</li>";
    echo "<li>Script'i tekrar çalıştırın</li>";
    echo "</ul>";
    
    saveLog("Database foreign key constraints partially applied. $errorCount errors occurred.", 'warning', 'apply_foreign_keys.php', $_SESSION['user_id']);
}

echo "<h2>Test Queries</h2>";
echo "<p>Foreign key'lerin çalıştığını test etmek için:</p>";
echo "<code style='display: block; background: #f5f5f5; padding: 10px; margin: 10px 0;'>";
echo "-- Foreign key durumu kontrol<br>";
echo "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME<br>";
echo "FROM information_schema.KEY_COLUMN_USAGE<br>";
echo "WHERE REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = 'odeme_takip';";
echo "</code>";

echo "<h2>Sonraki Adımlar</h2>";
echo "<p>1. Bu dosyayı güvenlik için silin: <code>apply_foreign_keys.php</code></p>";
echo "<p>2. Data integrity'yi test edin</p>";
echo "<p>3. Orphaned records'ın artık oluşmadığını kontrol edin</p>";

echo "<hr>";
echo "<p><small>Bu script sadece bir kez çalıştırılmalıdır. Tarih: " . date('Y-m-d H:i:s') . "</small></p>";
?>