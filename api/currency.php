<?php

require_once __DIR__ . '/../config.php';
checkLogin();

// Güncel kur bilgisini al
function getExchangeRate($from_currency, $to_currency)
{
    global $pdo;

    // Eğer aynı para birimi ise 1 döndür
    if ($from_currency === $to_currency) {
        return 1;
    }

    // Para birimlerini küçük harfe çevir
    $from_currency = strtolower($from_currency);
    $to_currency = strtolower($to_currency);

    // Önce veritabanından son kaydedilen kuru kontrol et
    $stmt = $pdo->prepare("SELECT rate FROM exchange_rates 
                          WHERE from_currency = ? AND to_currency = ? 
                          AND date = CURDATE()
                          ORDER BY date DESC LIMIT 1");
    $stmt->execute([$from_currency, $to_currency]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['rate'];
    }

    try {
        // API'den güncel kur bilgisini al
        $api_url = "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/{$from_currency}.json";
        $response = @file_get_contents($api_url);
        
        if ($response === false) {
            throw new Exception("Kur bilgisi alınamadı");
        }

        $data = json_decode($response, true);

        if ($data && isset($data[$from_currency][$to_currency])) {
            $rate = $data[$from_currency][$to_currency];

            // Kur bilgisini veritabanına kaydet
            $stmt = $pdo->prepare("INSERT INTO exchange_rates (from_currency, to_currency, rate, date) 
                                  VALUES (?, ?, ?, CURDATE())");
            $stmt->execute([$from_currency, $to_currency, $rate]);

            return $rate;
        }
    } catch (Exception $e) {
        // API'den alınamazsa son kaydedilen kuru kontrol et
        $stmt = $pdo->prepare("SELECT rate FROM exchange_rates 
                              WHERE from_currency = ? AND to_currency = ? 
                              ORDER BY date DESC LIMIT 1");
        $stmt->execute([$from_currency, $to_currency]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['rate'];
        }

        // Hiç kur bulunamazsa varsayılan değerler
        $default_rates = [
            'usd' => ['try' => 31.5],
            'eur' => ['try' => 34.2],
            'gbp' => ['try' => 39.8]
        ];

        if (isset($default_rates[$from_currency][$to_currency])) {
            return $default_rates[$from_currency][$to_currency];
        }
    }

    // Hiçbir şekilde kur bulunamazsa 1 döndür
    return 1;
}
