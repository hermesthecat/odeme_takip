<?php

require_once __DIR__ . '/../config.php';
checkLogin();


function addSaving()
{
    global $pdo, $user_id;

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('saving.name'));
    }

    if (isset($_POST['target_amount'])) {
        $target_amount = validateRequired($_POST['target_amount'] ?? null, t('saving.target_amount'));
        $target_amount = validateNumeric($target_amount, t('saving.target_amount'));
        $target_amount = validateMinValue($target_amount, 0, t('saving.target_amount'));
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, t('saving.currency'));
        $currency = validateCurrency($currency, t('saving.currency'));
    }

    if (isset($_POST['start_date'])) {
        $start_date = validateRequired($_POST['start_date'] ?? null, t('saving.start_date'));
        $start_date = validateDate($start_date, t('saving.start_date'));
    }

    if (isset($_POST['target_date'])) {
        $target_date = validateRequired($_POST['target_date'] ?? null, t('saving.target_date'));
        $target_date = validateDate($target_date, t('saving.target_date'));
        validateDateRange($start_date, $target_date);
    }

    $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, target_amount, currency, start_date, target_date) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $target_amount, $currency, $start_date, $target_date])) {
        return true;
    } else {
        throw new Exception(t('saving.add_error'));
    }
}

function deleteSaving()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("DELETE FROM savings WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['id'], $user_id])) {
        return true;
    } else {
        throw new Exception(t('saving.delete_error'));
    }
}

function loadSavings()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception(t('saving.load_error'));
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateSaving()
{
    global $pdo, $user_id;

    // Birikim bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM savings WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$_POST['id'], $user_id])) {
        throw new Exception(t('saving.not_found'));
    }
    $saving = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$saving) {
        throw new Exception(t('saving.not_found'));
    }

    // Mevcut tutarı doğrula
    $current_amount = validateRequired($_POST['current_amount'] ?? null, t('saving.current_amount'));
    $current_amount = validateNumeric($current_amount, t('saving.current_amount'));
    $current_amount = validateMinValue($current_amount, 0, t('saving.current_amount'));

    $stmt = $pdo->prepare("UPDATE savings SET current_amount = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$current_amount, $_POST['id'], $user_id])) {
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
        throw new Exception(t('saving.update_error'));
    }
}
