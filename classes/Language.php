<?php

class Language
{
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'tr';
    private $fallbackLang = 'en';
    private $availableLangs = ['tr', 'en'];

    private function __construct()
    {
        $this->loadLanguage();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setLanguage($lang)
    {
        if (in_array($lang, $this->availableLangs)) {
            $this->currentLang = $lang;
            $this->loadLanguage();

            // Dil tercihini session'a kaydet
            $_SESSION['lang'] = $lang;

            // Dil tercihini cookie'ye kaydet (30 gün)
            setcookie('lang', $lang, time() + (86400 * 30), '/');

            return true;
        }
        return false;
    }

    public function getCurrentLanguage()
    {
        return $this->currentLang;
    }

    public function getAvailableLanguages()
    {
        return $this->availableLangs;
    }

    private function loadLanguage()
    {
        // Ana dil dosyasını yükle
        $langFile = __DIR__ . '/../lang/' . $this->currentLang . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        } else {
            // Yedek dil dosyasını yükle
            $fallbackFile = __DIR__ . '/../lang/' . $this->fallbackLang . '.php';
            if (file_exists($fallbackFile)) {
                $this->translations = require $fallbackFile;
            }
        }
    }

    public function get($key, $params = [])
    {
        $translation = $this->translations;

        // Nokta notasyonunu kullanarak iç içe dizilere eriş
        foreach (explode('.', $key) as $segment) {
            if (isset($translation[$segment])) {
                $translation = $translation[$segment];
            } else {
                return $key; // Çeviri bulunamazsa anahtarı döndür
            }
        }

        // Parametre değişimini yap
        if (!empty($params) && is_string($translation)) {
            foreach ($params as $param => $value) {
                $translation = str_replace(':' . $param, $value, $translation);
            }
        }

        return $translation;
    }

    public function getLanguageName($code)
    {
        $languages = [
            'tr' => 'Türkçe',
            'en' => 'English'
        ];
        return $languages[$code] ?? $code;
    }

    // Kısaltma fonksiyonu
    public static function t($key, $params = [])
    {
        return self::getInstance()->get($key, $params);
    }
}
