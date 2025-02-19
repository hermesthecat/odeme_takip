<?php

// Çıktı filtreleme fonksiyonları
function sanitizeOutput($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeOutput($value);
        }
        return $data;
    }

    if (is_object($data)) {
        foreach ($data as $key => $value) {
            $data->$key = sanitizeOutput($value);
        }
        return $data;
    }

    if (is_string($data)) {
        // HTML karakterlerini encode et
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return $data;
}
