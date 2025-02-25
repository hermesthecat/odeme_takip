<?php

require_once __DIR__ . '/../config.php';
checkLogin();


function addSaving()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

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

    // Kur bilgisini al
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception(t('income.rate_error'));
        }
    }

    $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, target_amount, currency, start_date, target_date, exchange_rate, update_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'initial')");

    if ($stmt->execute([$user_id, $name, $target_amount, $currency, $start_date, $target_date, $exchange_rate])) {
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

    $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? AND parent_id IS NULL");
    if (!$stmt->execute([$user_id])) {
        throw new Exception(t('saving.load_error'));
    }
    $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $savings;
}

function updateSaving()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Get original saving
    $stmt = $pdo->prepare("SELECT * FROM savings WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$_POST['id'], $user_id])) {
        throw new Exception(t('saving.not_found'));
    }
    $saving = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$saving) {
        throw new Exception(t('saving.not_found'));
    }

    // Validate current amount
    $current_amount = validateRequired($_POST['current_amount'] ?? null, t('saving.current_amount'));
    $current_amount = validateNumeric($current_amount, t('saving.current_amount'));
    $current_amount = validateMinValue($current_amount, 0, t('saving.current_amount'));

    $currency = $saving['currency'];

    // Kur bilgisini al
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception(t('income.rate_error'));
        }
    }

    // Create new saving record
    $stmt = $pdo->prepare("INSERT INTO savings (user_id, parent_id, name, target_amount, current_amount, currency, start_date, target_date, exchange_rate, update_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$user_id, $saving['id'], $saving['name'], $saving['target_amount'], $current_amount, $saving['currency'], $saving['start_date'], $saving['target_date'], $exchange_rate, 'update'])) {

        // Update original saving record
        $stmt = $pdo->prepare("UPDATE savings SET current_amount = ?, exchange_rate = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$current_amount, $exchange_rate, $saving['id'], $user_id]);
        return true;
    } else {
        throw new Exception(t('saving.update_error'));
    }

    // Create new saving record
    $stmt = $pdo->prepare("INSERT INTO savings (user_id, parent_id, name, target_amount, current_amount, currency, start_date, target_date, exchange_rate, update_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$user_id, $saving['id'], $saving['name'], $saving['target_amount'], $current_amount, $saving['currency'], $saving['start_date'], $saving['target_date'], $exchange_rate, 'update'])) {

        // Update original saving record
        $stmt = $pdo->prepare("UPDATE savings SET current_amount = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$current_amount, $saving['id'], $user_id]);
        return true;
    } else {
        throw new Exception(t('saving.update_error'));
    }
}


function updateFullSaving()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Get original saving
    $stmt = $pdo->prepare("SELECT * FROM savings WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$_POST['id'], $user_id])) {
        throw new Exception(t('saving.not_found'));
    }
    $saving = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$saving) {
        throw new Exception(t('saving.not_found'));
    }

    // Validate data
    $name = validateRequired($_POST['name'] ?? null, t('saving.name'));
    $target_amount = validateRequired($_POST['target_amount'] ?? null, t('saving.target_amount'));
    $target_amount = validateNumeric($target_amount, t('saving.target_amount'));
    $target_amount = validateMinValue($target_amount, 0, t('saving.target_amount'));
    $current_amount = validateRequired($_POST['current_amount'] ?? null, t('saving.current_amount'));
    $current_amount = validateNumeric($current_amount, t('saving.current_amount'));
    $current_amount = validateMinValue($current_amount, 0, t('saving.current_amount'));
    $currency = validateRequired($_POST['currency'] ?? null, t('saving.currency'));
    $currency = validateCurrency($currency, t('saving.currency'));
    $start_date = validateRequired($_POST['start_date'] ?? null, t('saving.start_date'));
    $start_date = validateDate($start_date, t('saving.start_date'));
    $target_date = validateRequired($_POST['target_date'] ?? null, t('saving.target_date'));
    $target_date = validateDate($target_date, t('saving.target_date'));
    validateDateRange($start_date, $target_date);

    // Kur bilgisini al
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception(t('income.rate_error'));
        }
    }

    // Create new saving record
    $stmt = $pdo->prepare("INSERT INTO savings (user_id, parent_id, name, target_amount, current_amount, currency, start_date, target_date, exchange_rate, update_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $saving['id'], $name, $target_amount, $current_amount, $currency, $start_date, $target_date, $exchange_rate, 'update'])) {
        // Update original saving record
        $stmt = $pdo->prepare("UPDATE savings SET current_amount = ?, exchange_rate = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$current_amount, $exchange_rate, $saving['id'], $user_id]);
        return true;
    } else {
        throw new Exception(t('saving.update_error'));
    }
}

function getSavingsHistory($saving_id)
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? AND (id = ? OR parent_id = ?) ORDER BY created_at ASC");
    $stmt->execute([$user_id, $saving_id, $saving_id]);

    if (!$stmt) {
        throw new Exception(t('saving.load_error'));
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
