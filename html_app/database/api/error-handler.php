<?php
class ErrorHandler {
    public static function init() {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // Error is not specified in error_reporting
            return false;
        }

        $error = self::formatError($errstr, $errfile, $errline);
        self::sendResponse('error', $error);
        return true;
    }

    public static function handleException($exception) {
        $error = self::formatException($exception);
        self::sendResponse('error', $error);
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && $error['type'] === E_ERROR) {
            $errorInfo = self::formatError(
                $error['message'],
                $error['file'],
                $error['line']
            );
            self::sendResponse('error', $errorInfo);
        }
    }

    private static function formatError($message, $file, $line): array {
        return [
            'type' => 'PHP Error',
            'message' => $message,
            'file' => self::formatFilePath($file),
            'line' => $line,
            'help' => self::getHelpContent($message)
        ];
    }

    private static function formatException($exception): array {
        return [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => self::formatFilePath($exception->getFile()),
            'line' => $exception->getLine(),
            'trace' => self::formatTrace($exception->getTrace()),
            'help' => self::getHelpContent($exception->getMessage())
        ];
    }

    private static function formatFilePath($path): string {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
    }

    private static function formatTrace(array $trace): array {
        return array_map(function($item) {
            return [
                'function' => $item['function'] ?? '',
                'file' => isset($item['file']) ? self::formatFilePath($item['file']) : '',
                'line' => $item['line'] ?? ''
            ];
        }, $trace);
    }

    private static function getHelpContent(string $message): string {
        $help = '<h4>Çözüm Önerileri</h4><ul>';

        // Database connection errors
        if (stripos($message, 'access denied') !== false) {
            $help .= '
                <li>Veritabanı kullanıcı adı ve şifresini kontrol edin</li>
                <li>Kullanıcının yeterli yetkiye sahip olduğunu doğrulayın</li>
                <li>MySQL sunucusunun çalıştığından emin olun</li>
            ';
        }

        // Connection refused errors
        if (stripos($message, 'connection refused') !== false) {
            $help .= '
                <li>MySQL sunucusunun çalıştığını kontrol edin</li>
                <li>Host adresinin doğru olduğundan emin olun</li>
                <li>Port numarasını kontrol edin</li>
            ';
        }

        // Duplicate entry errors
        if (stripos($message, 'duplicate entry') !== false) {
            $help .= '
                <li>Bu kayıt zaten mevcut</li>
                <li>Benzersiz alanları kontrol edin</li>
                <li>Veriyi güncellemek için farklı bir yöntem kullanın</li>
            ';
        }

        // Table not found errors
        if (stripos($message, 'table') !== false && stripos($message, 'doesn\'t exist') !== false) {
            $help .= '
                <li>Veritabanı şemasının oluşturulduğundan emin olun</li>
                <li>Tablo adının doğru olduğunu kontrol edin</li>
                <li>Migration scriptini çalıştırın</li>
            ';
        }

        // Default help
        if (strpos($help, '<li>') === false) {
            $help .= '
                <li>config.php dosyasındaki veritabanı ayarlarını kontrol edin</li>
                <li>Tüm gerekli tabloların oluşturulduğundan emin olun</li>
                <li>PHP error loglarını kontrol edin</li>
            ';
        }

        $help .= '</ul>';
        return $help;
    }

    private static function sendResponse(string $status, array $data): void {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($status === 'error' ? 500 : 200);
        }

        echo json_encode([
            'status' => $status,
            'error' => $data,
            'timestamp' => date('c')
        ]);
    }
}