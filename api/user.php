<?php

require_once __DIR__ . '/../config.php';
checkLogin();

function updateUserSettings()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("UPDATE users SET base_currency = ?, theme_preference = ? WHERE id = ?");
    if ($stmt->execute([$_POST['base_currency'], $_POST['theme_preference'], $user_id])) {

        // set session lang based on base_currency
        if ($_POST['base_currency'] == 'TRY') {
            $_SESSION['lang'] = 'tr';
        } else {
            $_SESSION['lang'] = 'en';
        }

        return true;
    } else {
        return false;
    }
}
