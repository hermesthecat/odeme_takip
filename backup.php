<?php
/**
 * Pecunia Database Backup Script
 * @author A. Kerem Gök
 * @date 2025-01-25
 * @description Automated database backup with compression and cleanup
 */

require_once 'config.php';

class PecuniaBackup
{
    private $pdo;
    private $backupDir;
    private $config;
    
    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->backupDir = __DIR__ . '/backups';
        $this->config = [
            'max_backups' => 10, // Keep last 10 backups
            'compress' => true,
            'include_data' => true,
            'exclude_tables' => ['logs'], // Optional: exclude log table
        ];
        
        $this->createBackupDirectory();
    }
    
    /**
     * Create full database backup
     */
    public function createBackup($includeData = true, $compress = true)
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "pecunia_backup_{$timestamp}.sql";
            $filepath = $this->backupDir . '/' . $filename;
            
            echo "🗄️ Creating database backup...\n";
            echo "📅 Timestamp: {$timestamp}\n";
            echo "📁 Location: {$filepath}\n\n";
            
            // Get all tables
            $tables = $this->getAllTables();
            echo "📊 Found " . count($tables) . " tables to backup\n";
            
            $sql = $this->generateBackupSQL($tables, $includeData);
            
            // Write to file
            file_put_contents($filepath, $sql);
            
            $fileSize = $this->formatFileSize(filesize($filepath));
            echo "✅ Backup created successfully! Size: {$fileSize}\n";
            
            // Compress if requested
            if ($compress) {
                $compressedFile = $this->compressBackup($filepath);
                if ($compressedFile) {
                    unlink($filepath); // Remove uncompressed version
                    $filepath = $compressedFile;
                    $fileSize = $this->formatFileSize(filesize($filepath));
                    echo "🗜️ Backup compressed! Final size: {$fileSize}\n";
                }
            }
            
            // Cleanup old backups
            $this->cleanupOldBackups();
            
            echo "\n🎉 Backup process completed successfully!\n";
            echo "📄 Backup file: " . basename($filepath) . "\n";
            
            return $filepath;
            
        } catch (Exception $e) {
            echo "❌ Backup failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Restore database from backup
     */
    public function restoreBackup($backupFile)
    {
        try {
            if (!file_exists($backupFile)) {
                throw new Exception("Backup file not found: {$backupFile}");
            }
            
            echo "🔄 Restoring database from backup...\n";
            echo "📄 Backup file: " . basename($backupFile) . "\n";
            
            // Check if file is compressed
            if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz') {
                $sql = gzfile($backupFile);
                $sql = implode('', $sql);
            } else {
                $sql = file_get_contents($backupFile);
            }
            
            // Disable foreign key checks temporarily
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            
            // Split and execute SQL statements
            $statements = $this->splitSqlStatements($sql);
            $executed = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->pdo->exec($statement);
                    $executed++;
                }
            }
            
            // Re-enable foreign key checks
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            
            echo "✅ Database restored successfully!\n";
            echo "📊 Executed {$executed} SQL statements\n";
            
            return true;
            
        } catch (Exception $e) {
            echo "❌ Restore failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups()
    {
        $backups = glob($this->backupDir . '/pecunia_backup_*.{sql,sql.gz}', GLOB_BRACE);
        
        if (empty($backups)) {
            echo "📂 No backups found\n";
            return [];
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        echo "📋 Available backups:\n";
        echo str_repeat('=', 60) . "\n";
        
        foreach ($backups as $index => $backup) {
            $filename = basename($backup);
            $size = $this->formatFileSize(filesize($backup));
            $date = date('Y-m-d H:i:s', filemtime($backup));
            
            echo sprintf("%2d. %-30s %10s %s\n", $index + 1, $filename, $size, $date);
        }
        
        return $backups;
    }
    
    /**
     * Auto backup with cron job support
     */
    public function autoBackup()
    {
        echo "🤖 Starting automatic backup...\n";
        echo "⏰ " . date('Y-m-d H:i:s') . "\n\n";
        
        $result = $this->createBackup(true, true);
        
        if ($result) {
            $this->logBackup($result, 'auto');
            echo "\n✅ Automatic backup completed successfully!\n";
        } else {
            echo "\n❌ Automatic backup failed!\n";
        }
        
        return $result;
    }
    
    /**
     * Private helper methods
     */
    private function createBackupDirectory()
    {
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    private function getAllTables()
    {
        $stmt = $this->pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Filter excluded tables
        return array_diff($tables, $this->config['exclude_tables']);
    }
    
    private function generateBackupSQL($tables, $includeData)
    {
        $sql = "-- Pecunia Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n\n";
        
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        
        foreach ($tables as $table) {
            echo "📊 Processing table: {$table}\n";
            
            // Table structure
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for table `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            $stmt = $this->pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch();
            $sql .= $row['Create Table'] . ";\n\n";
            
            // Table data
            if ($includeData) {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    $sql .= "-- Dumping data for table `{$table}`\n";
                    $sql .= "-- {$count} rows\n\n";
                    
                    $stmt = $this->pdo->query("SELECT * FROM `{$table}`");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $values = array_map([$this->pdo, 'quote'], array_values($row));
                        $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
        }
        
        $sql .= "COMMIT;\n";
        return $sql;
    }
    
    private function compressBackup($filepath)
    {
        if (!function_exists('gzencode')) {
            echo "⚠️ Warning: gzip compression not available\n";
            return false;
        }
        
        $compressedFile = $filepath . '.gz';
        $data = file_get_contents($filepath);
        $compressed = gzencode($data, 9);
        
        if (file_put_contents($compressedFile, $compressed)) {
            return $compressedFile;
        }
        
        return false;
    }
    
    private function cleanupOldBackups()
    {
        $backups = glob($this->backupDir . '/pecunia_backup_*.{sql,sql.gz}', GLOB_BRACE);
        
        if (count($backups) <= $this->config['max_backups']) {
            return;
        }
        
        // Sort by date (oldest first)
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $toDelete = array_slice($backups, 0, count($backups) - $this->config['max_backups']);
        
        foreach ($toDelete as $file) {
            unlink($file);
            echo "🗑️ Cleaned up old backup: " . basename($file) . "\n";
        }
    }
    
    private function splitSqlStatements($sql)
    {
        return array_filter(
            array_map('trim', explode(';', $sql)),
            function($statement) {
                return !empty($statement) && !preg_match('/^--/', $statement);
            }
        );
    }
    
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function logBackup($filepath, $type = 'manual')
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO logs (user_id, method, type, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                0, // System user
                'backup',
                $type,
                'Database backup created: ' . basename($filepath),
                $_SERVER['REMOTE_ADDR'] ?? 'CLI'
            ]);
        } catch (Exception $e) {
            // Ignore logging errors
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'backup';
    $backup = new PecuniaBackup();
    
    switch ($action) {
        case 'backup':
        case 'create':
            $compress = isset($argv[2]) ? ($argv[2] === 'true' || $argv[2] === '1') : true;
            $backup->createBackup(true, $compress);
            break;
            
        case 'restore':
            if (!isset($argv[2])) {
                echo "Usage: php backup.php restore <backup_file>\n";
                exit(1);
            }
            $backup->restoreBackup($argv[2]);
            break;
            
        case 'list':
            $backup->listBackups();
            break;
            
        case 'auto':
            $backup->autoBackup();
            break;
            
        default:
            echo "Pecunia Backup Script\n";
            echo "Usage:\n";
            echo "  php backup.php backup [compress]  - Create backup\n";
            echo "  php backup.php restore <file>     - Restore from backup\n";
            echo "  php backup.php list               - List available backups\n";
            echo "  php backup.php auto               - Auto backup (for cron)\n";
            break;
    }
} else {
    // Web interface would go here if needed
    die("This script should be run from command line.\n");
}
?>