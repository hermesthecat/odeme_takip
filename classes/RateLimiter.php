<?php

/**
 * MySQL Database-based Rate Limiter
 * Performant ve scalable rate limiting sistemi
 */
class RateLimiter
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Rate limit kontrolü yap
     * @param string $endpoint API endpoint veya action
     * @param string $identifier IP, user_id veya combined identifier
     * @param string $identifierType 'ip', 'user', 'api_key', 'combined'
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int, 'limit' => int]
     */
    public function checkLimit($endpoint, $identifier, $identifierType = 'ip')
    {
        try {
            // Rate limit kuralını al
            $rule = $this->getRateLimitRule($endpoint, $identifierType);
            if (!$rule) {
                // Kural bulunamadı, izin ver
                return [
                    'allowed' => true,
                    'remaining' => 999,
                    'reset_time' => time() + 300,
                    'limit' => 999
                ];
            }

            $windowStart = $this->getWindowStart($rule['window_minutes']);
            $expiresAt = date('Y-m-d H:i:s', strtotime($windowStart) + ($rule['window_minutes'] * 60));

            // Mevcut request sayısını al veya oluştur
            $currentCount = $this->getCurrentRequestCount($identifier, $endpoint, $windowStart);
            
            if ($currentCount >= $rule['max_requests']) {
                // Limit aşıldı, violation log
                $this->logViolation($identifier, $endpoint, $currentCount, $rule['max_requests']);
                
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'reset_time' => strtotime($expiresAt),
                    'limit' => $rule['max_requests']
                ];
            }

            // Request sayısını artır
            $newCount = $this->incrementRequestCount($identifier, $endpoint, $windowStart, $expiresAt);
            
            return [
                'allowed' => true,
                'remaining' => max(0, $rule['max_requests'] - $newCount),
                'reset_time' => strtotime($expiresAt),
                'limit' => $rule['max_requests']
            ];

        } catch (Exception $e) {
            // Error durumunda izin ver ama log
            error_log("RateLimiter Error: " . $e->getMessage());
            return [
                'allowed' => true,
                'remaining' => 999,
                'reset_time' => time() + 300,
                'limit' => 999
            ];
        }
    }

    /**
     * Quick rate limit check - sadece izin verip vermeyeceğimizi döndürür
     */
    public function isAllowed($endpoint, $identifier, $identifierType = 'ip')
    {
        $result = $this->checkLimit($endpoint, $identifier, $identifierType);
        return $result['allowed'];
    }

    /**
     * Rate limit kuralını getir
     */
    private function getRateLimitRule($endpoint, $identifierType)
    {
        $stmt = $this->pdo->prepare("
            SELECT max_requests, window_minutes 
            FROM rate_limit_rules 
            WHERE endpoint = :endpoint 
            AND identifier_type = :identifier_type 
            AND is_active = 1
            LIMIT 1
        ");
        
        $stmt->execute([
            ':endpoint' => $endpoint,
            ':identifier_type' => $identifierType
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Time window başlangıcını hesapla
     */
    private function getWindowStart($windowMinutes)
    {
        $now = time();
        $windowSeconds = $windowMinutes * 60;
        $windowStart = floor($now / $windowSeconds) * $windowSeconds;
        return date('Y-m-d H:i:s', $windowStart);
    }

    /**
     * Mevcut request sayısını al
     */
    private function getCurrentRequestCount($identifier, $endpoint, $windowStart)
    {
        $stmt = $this->pdo->prepare("
            SELECT request_count 
            FROM rate_limits 
            WHERE identifier = :identifier 
            AND endpoint = :endpoint 
            AND window_start = :window_start
            LIMIT 1
        ");
        
        $stmt->execute([
            ':identifier' => $identifier,
            ':endpoint' => $endpoint,
            ':window_start' => $windowStart
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['request_count'] : 0;
    }

    /**
     * Request sayısını artır
     */
    private function incrementRequestCount($identifier, $endpoint, $windowStart, $expiresAt)
    {
        // MySQL'in ON DUPLICATE KEY UPDATE özelliğini kullan
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, endpoint, request_count, window_start, expires_at) 
            VALUES (:identifier, :endpoint, 1, :window_start, :expires_at)
            ON DUPLICATE KEY UPDATE 
                request_count = request_count + 1,
                expires_at = :expires_at_update
        ");
        
        $stmt->execute([
            ':identifier' => $identifier,
            ':endpoint' => $endpoint,
            ':window_start' => $windowStart,
            ':expires_at' => $expiresAt,
            ':expires_at_update' => $expiresAt
        ]);

        // Güncellenmiş sayıyı döndür
        return $this->getCurrentRequestCount($identifier, $endpoint, $windowStart);
    }

    /**
     * Rate limit ihlalini logla
     */
    private function logViolation($identifier, $endpoint, $currentCount, $maxAllowed)
    {
        global $user_id;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limit_violations 
            (identifier, endpoint, request_count, max_allowed, user_agent, ip_address, user_id) 
            VALUES (:identifier, :endpoint, :current_count, :max_allowed, :user_agent, :ip_address, :user_id)
        ");
        
        $stmt->execute([
            ':identifier' => $identifier,
            ':endpoint' => $endpoint,
            ':current_count' => $currentCount,
            ':max_allowed' => $maxAllowed,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':ip_address' => $this->getClientIP(),
            ':user_id' => $user_id ?? null
        ]);
    }

    /**
     * Client IP adresini güvenli şekilde al
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Rate limit bilgilerini header olarak ekle
     */
    public function addRateLimitHeaders($result)
    {
        header('X-RateLimit-Limit: ' . $result['limit']);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset_time']);
        
        if (!$result['allowed']) {
            header('Retry-After: ' . ($result['reset_time'] - time()));
        }
    }

    /**
     * 429 Too Many Requests response gönder
     */
    public function sendRateLimitResponse($result)
    {
        $this->addRateLimitHeaders($result);
        http_response_code(429);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'message' => 'Too many requests. Please try again later.',
            'details' => [
                'limit' => $result['limit'],
                'remaining' => $result['remaining'],
                'reset_time' => $result['reset_time'],
                'retry_after' => $result['reset_time'] - time()
            ]
        ]);
        exit;
    }

    /**
     * Combined identifier oluştur (IP + User)
     */
    public function getCombinedIdentifier($userID = null)
    {
        global $user_id;
        $ip = $this->getClientIP();
        $uid = $userID ?? $user_id ?? 'anonymous';
        return $ip . ':' . $uid;
    }

    /**
     * Expired kayıtları temizle (performans için)
     */
    public function cleanup()
    {
        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()");
        $stmt->execute();
        
        // 30 günden eski violation logları temizle
        $stmt = $this->pdo->prepare("DELETE FROM rate_limit_violations WHERE violation_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
    }

    /**
     * Rate limit istatistikleri al
     */
    public function getStats($identifier = null, $hours = 24)
    {
        $whereClause = $identifier ? "WHERE identifier = :identifier" : "";
        $params = $identifier ? [':identifier' => $identifier, ':hours' => $hours] : [':hours' => $hours];
        
        $stmt = $this->pdo->prepare("
            SELECT 
                endpoint,
                COUNT(*) as violation_count,
                MAX(violation_time) as last_violation
            FROM rate_limit_violations 
            $whereClause 
            AND violation_time > DATE_SUB(NOW(), INTERVAL :hours HOUR)
            GROUP BY endpoint
            ORDER BY violation_count DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}