<?php

/**
 * Para birimi dönüşüm API'si
 * Currency conversion API
 * 
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/log.php';
checkLogin();

/**
 * Güncel kur bilgisini al
 * Get current exchange rate
 * 
 * @param string $from_currency - Kaynak para birimi / Source currency
 * @param string $to_currency - Hedef para birimi / Target currency
 * @return float - Dönüşüm oranı / Exchange rate
 */
function getExchangeRate($from_currency, $to_currency)
{
    global $pdo;

    // Eğer aynı para birimi ise 1 döndür
    // If same currency, return 1
    if ($from_currency === $to_currency) {
        return 1;
    }

    // Para birimlerini küçük harfe çevir
    // Convert currencies to lowercase
    $from_currency = strtolower($from_currency);
    $to_currency = strtolower($to_currency);

    // Önce veritabanından son kaydedilen kuru kontrol et
    // First check the last saved rate from database
    $stmt = $pdo->prepare("SELECT rate FROM exchange_rates 
                          WHERE from_currency = ? AND to_currency = ? 
                          AND date = CURDATE()
                          ORDER BY date DESC LIMIT 1");
    $stmt->execute([$from_currency, $to_currency]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        saveLog("Para birimi kuru veritabanından alındı: " . $from_currency . " to " . $to_currency . " rate: " . $result['rate'], 'info', 'getExchangeRate', $_SESSION['user_id']);
        // Exchange rate retrieved from database
        return $result['rate'];
    }

    try {
        // API'den güncel kur bilgisini al
        // Get current exchange rate from API
        $api_url = "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/{$from_currency}.json";
        $response = @file_get_contents($api_url);

        if ($response === false) {
            saveLog("Para birimi kuru api'den alınırken hata: " . $from_currency . " to " . $to_currency, 'error', 'getExchangeRate', $_SESSION['user_id']);
            // Error while fetching exchange rate from API
            throw new Exception(t('currency.rate_fetch_error'));
        }

        $data = json_decode($response, true);

        if ($data && isset($data[$from_currency][$to_currency])) {
            $rate = $data[$from_currency][$to_currency];

            // Kur bilgisini veritabanına kaydet
            // Save exchange rate to database
            $stmt = $pdo->prepare("INSERT INTO exchange_rates (from_currency, to_currency, rate, date) 
                                  VALUES (?, ?, ?, CURDATE())");
            $stmt->execute([$from_currency, $to_currency, $rate]);

            saveLog("Para birimi kuru veritabanına kaydedildi: " . $from_currency . " to " . $to_currency . " rate: " . $rate, 'info', 'getExchangeRate', $_SESSION['user_id']);
            // Exchange rate saved to database
            return $rate;
        }
    } catch (Exception $e) {
        // API'den alınamazsa son kaydedilen kuru kontrol et
        // If cannot get from API, check last saved rate
        $stmt = $pdo->prepare("SELECT rate FROM exchange_rates 
                              WHERE from_currency = ? AND to_currency = ? 
                              ORDER BY date DESC LIMIT 1");
        $stmt->execute([$from_currency, $to_currency]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['rate'];
        }
    }

    // Hiçbir şekilde kur bulunamazsa 1 döndür ve hata mesajı döndür
    // If exchange rate cannot be found in any way, return 1 and return error message
    return 1;
}
