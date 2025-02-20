<?php

require_once __DIR__ . '/../config.php';
checkLogin();


function addSaving()
{
    global $pdo, $user_id;

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, "Birikim adı");
    }

    if (isset($_POST['target_amount'])) {
        $target_amount = validateRequired($_POST['target_amount'] ?? null, "Hedef tutar");
        $target_amount = validateNumeric($target_amount, "Hedef tutar");
        $target_amount = validateMinValue($target_amount, 0, "Hedef tutar");
    }

    if (isset($_POST['current_amount'])) {
        $current_amount = validateRequired($_POST['current_amount'] ?? null, "Mevcut tutar");
        $current_amount = validateNumeric($current_amount, "Mevcut tutar");
        $current_amount = validateMinValue($current_amount, 0, "Mevcut tutar");
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, "Para birimi");
        $currency = validateCurrency($currency, "Para birimi");
    }

    if (isset($_POST['start_date'])) {
        $start_date = validateRequired($_POST['start_date'] ?? null, "Başlangıç tarihi");
        $start_date = validateDate($start_date, "Başlangıç tarihi");
    }

    if (isset($_POST['target_date'])) {
        $target_date = validateRequired($_POST['target_date'] ?? null, "Hedef tarihi");
        $target_date = validateDate($target_date, "Hedef tarihi");
        validateDateRange($start_date, $target_date);
    }


    $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, target_amount, currency, start_date, target_date) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $target_amount, $currency, $start_date, $target_date])) {
        return true;
    } else {
        return false;
    }
}

function deleteSaving()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("DELETE FROM savings WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['id'], $user_id])) {
        return true;
    } else {
        return false;
    }
}

function loadSavings()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $savings;
}

function updateSaving()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("UPDATE savings SET current_amount = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['current_amount'], $_POST['id'], $user_id])) {
        return true;
    } else {
        return false;
    }
}

function updateFullSaving()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("UPDATE savings SET name = ?, target_amount = ?, current_amount = ?, currency = ?, start_date = ?, target_date = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([
        $_POST['name'],
        $_POST['target_amount'],
        $_POST['current_amount'],
        $_POST['currency'],
        $_POST['start_date'],
        $_POST['target_date'],
        $_POST['id'],
        $user_id
    ])) {
        return true;
    } else {
        return false;
    }
}
