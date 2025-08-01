<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/log.php';
checkLogin();

function addPayment()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('payment.name'));
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, t('payment.amount'));
        $amount = validateNumeric($amount, t('payment.amount'));
        $amount = validateMinValue($amount, 0, t('payment.amount'));
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, t('payment.currency'));
        $currency = validateCurrency($currency, t('payment.currency'));
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, t('payment.date'));
        $first_date = validateDate($first_date, t('payment.date'));
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, t('payment.frequency'));
        $frequency = validateFrequency($frequency, t('payment.frequency'));
    } else {
        $frequency = 'none';
    }

    if (isset($_POST['end_date']) && $_POST['end_date'] !== '') {
        $end_date = validateDate($_POST['end_date'], t('payment.end_date'));
        validateDateRange($first_date, $end_date);
    }

    if (isset($_POST['card_id'])) {
        $card_id = validateRequired($_POST['card_id'] ?? null, t('payment.card'));
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini al
        $exchange_rate = null;
        if ($currency !== $base_currency) {
            $exchange_rate = getExchangeRate($currency, $base_currency);
            if (!$exchange_rate) {
                throw new Exception(t('payment.rate_error'));
            }
        }

        // Ana kaydı ekle
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate, status, card_id) 
                             VALUES (?, NULL, ?, ?, ?, ?, ?, ?, 'pending', ?)");

        if (!$stmt->execute([
            $user_id,
            $name,
            $amount,
            $currency,
            $first_date,
            $frequency,
            $exchange_rate,
            $card_id
        ])) {
            throw new Exception(t('payment.add_error'));
        }

        $parent_id = $pdo->lastInsertId();
        saveLog("Ana ödeme eklendi: " . $name, 'info', 'addPayment', $_SESSION['user_id']);

        // Tekrarlı kayıtlar için
        if ($frequency !== 'none' && isset($end_date) && $end_date > $first_date) {
            $month_interval = getMonthInterval($frequency);
            $total_months = getMonthDifference($first_date, $end_date);
            $repeat_count = floor($total_months / $month_interval);

            // Child kayıtları ekle
            if ($repeat_count > 0) {
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate, status, card_id) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");

                for ($i = 1; $i <= $repeat_count; $i++) {
                    $payment_date = calculateNextPaymentDate($first_date, $i * $month_interval);

                    // Bitiş tarihini geçmemesi için kontrol
                    if ($payment_date <= $end_date) {
                        if (!$stmt->execute([
                            $user_id,
                            $parent_id,
                            $name,
                            $amount,
                            $currency,
                            $payment_date,
                            $frequency,
                            $exchange_rate,
                            $card_id
                        ])) {
                            throw new Exception(t('payment.add_recurring_error'));
                        }
                        saveLog("Tekrarlı ödeme eklendi: " . $name . " - " . $payment_date, 'info', 'addPayment', $_SESSION['user_id']);
                    }
                }
            }
        }

        $pdo->commit();
        
        // Cache invalidation - ödeme eklenen ay cache'ini temizle
        invalidateSummaryCacheForDate($user_id, $first_date);
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function deletePayment()
{
    global $pdo, $user_id;

    $pdo->beginTransaction();

    try {
        $id = $_POST['id'];
        $delete_children = isset($_POST['delete_children']) && $_POST['delete_children'] === 'true';
        
        // Silmeden önce tarihi al - cache invalidation için
        $date_stmt = $pdo->prepare("SELECT first_date FROM payments WHERE id = ? AND user_id = ?");
        $date_stmt->execute([$id, $user_id]);
        $payment_record = $date_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment_record) {
            throw new Exception(t('payment.not_found'));
        }

        if ($delete_children) {
            // Önce child kayıtları sil
            $stmt = $pdo->prepare("DELETE FROM payments WHERE (parent_id = ?) AND user_id = ?");
            if (!$stmt->execute([$id, $user_id])) {
                throw new Exception(t('payment.delete_error'));
                saveLog("Ödeme silme hatası ($id): " . $e->getMessage(), 'error', 'deletePayment', $_SESSION['user_id']);
            }

            // sonra parent kaydını sil
            $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
            if (!$stmt->execute([$id, $user_id])) {
                throw new Exception(t('payment.delete_error'));
                saveLog("Ödeme silme hatası ($id): " . $e->getMessage(), 'error', 'deletePayment', $_SESSION['user_id']);
            }
        } else {

            // check if the payment is a parent
            $stmt = $pdo->prepare("SELECT parent_id FROM payments WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            // if the payment is not a parent, delete only the payment
            if ($payment['parent_id'] !== null) {
                // Sadece seçili kaydı sil
                $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
                if (!$stmt->execute([$id, $user_id])) {
                    throw new Exception(t('payment.delete_error'));
                    saveLog("Ödeme silme hatası ($id): " . $e->getMessage(), 'error', 'deletePayment', $_SESSION['user_id']);
                }
            } else {
                return false;
            }
        }

        $pdo->commit();
        
        // Cache invalidation - silinen ödemenin ayına ait cache'i temizle
        invalidateSummaryCacheForDate($user_id, $payment_record['first_date']);
        
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function loadPayments()
{
    global $pdo, $user_id, $month, $year;

    // Ödemeleri al
    $sql_payments = "SELECT p.*, c.name as card_name,
                            CASE 
                                WHEN p.parent_id IS NULL THEN (
                                    SELECT MIN(p2.first_date)
                                    FROM payments p2
                                    WHERE p2.parent_id = p.id
                                    AND p2.user_id = p.user_id
                                )
                                ELSE (
                                    SELECT MIN(p2.first_date)
                                    FROM payments p2
                                    WHERE p2.parent_id = p.parent_id
                                    AND p2.first_date > p.first_date
                                    AND p2.user_id = p.user_id
                                )
                            END as next_payment_date
                            FROM payments p 
                            LEFT JOIN card c ON p.card_id = c.id
                            WHERE p.user_id = ? 
                            AND MONTH(p.first_date) = ? 
                            AND YEAR(p.first_date) = ?
                            ORDER BY p.name ASC";

    $stmt_payments = $pdo->prepare($sql_payments);
    $stmt_payments->execute([$user_id, $month, $year]);
    $payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

    saveLog("Ödemeler alındı: " . $user_id . " " . $month . " " . $year, 'info', 'loadPayments', $_SESSION['user_id']);

    foreach ($payments as &$payment) {
        $payment['formatted_first_date'] = formatDate($payment['first_date']);
        $payment['formatted_next_payment_date'] = $payment['next_payment_date'] ? formatDate($payment['next_payment_date']) : null;
    }
    unset($payment); // Referansı temizle

    return $payments;
}

function loadRecurringPayments()
{
    global $pdo, $user_id;

    // Tekrarlayan ödemeleri al
    $sql_recurring_payments = "SELECT 
        p1.id,
        p1.name,
        p1.amount,
        p1.currency,
        p1.frequency,
        (
            SELECT SUM(CASE 
                WHEN p2.currency = (SELECT base_currency FROM users WHERE id = p1.user_id) THEN p2.amount 
                ELSE p2.amount * COALESCE(p2.exchange_rate, 1) 
            END)
            FROM payments p2 
            WHERE (p2.parent_id = p1.id OR p2.id = p1.id)
            AND p2.user_id = p1.user_id
        ) as yearly_total,
        (
            SELECT SUM(CASE 
                WHEN p2.currency = (SELECT base_currency FROM users WHERE id = p1.user_id) THEN p2.amount 
                ELSE p2.amount * COALESCE(p2.exchange_rate, 1) 
            END)
            FROM payments p2 
            WHERE (p2.parent_id = p1.id OR p2.id = p1.id)
            AND p2.user_id = p1.user_id
            AND p2.status = 'pending'
        ) as unpaid_total,
        CONCAT(
            (SELECT COUNT(*) FROM payments p2 
             WHERE (p2.parent_id = p1.id OR p2.id = p1.id)
             AND p2.status = 'paid'
             AND p2.user_id = p1.user_id),
            '/',
            (SELECT COUNT(*) + 1 FROM payments p3 
             WHERE p3.parent_id = p1.id 
             AND p3.user_id = p1.user_id)
        ) as payment_status
    FROM payments p1 
    WHERE p1.user_id = ? 
    AND p1.frequency != 'none'
    AND p1.parent_id IS NULL
    AND p1.payment_power = 1
    ORDER BY p1.name ASC";

    $stmt_recurring_payments = $pdo->prepare($sql_recurring_payments);
    $stmt_recurring_payments->execute([$user_id]);
    $recurring_payments = $stmt_recurring_payments->fetchAll(PDO::FETCH_ASSOC);

    saveLog("Tekrarlayan ödemeler alındı: " . $user_id, 'info', 'loadRecurringPayments', $_SESSION['user_id']);

    foreach ($recurring_payments as &$recurring_payment) {
        if (isset($recurring_payment['first_date'])) {
            $recurring_payment['formatted_first_date'] = formatDate($recurring_payment['first_date']);
        }
    }
    unset($recurring_payment); // Referansı temizle

    return $recurring_payments;
}

function markPaymentPaid()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Ödeme bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception(t('payment.not_found'));
        saveLog("Ödeme bulunamadı: " . $_POST['id'], 'error', 'markPaymentPaid', $_SESSION['user_id']);
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini güncelle
        $exchange_rate = null;
        if ($payment['currency'] !== $base_currency) {
            $exchange_rate = getExchangeRate($payment['currency'], $base_currency);
            if (!$exchange_rate) {
                throw new Exception(t('payment.rate_error'));
                saveLog("Ödeme kuru hatası: " . $payment['currency'] . " to " . $base_currency, 'error', 'markPaymentPaid', $_SESSION['user_id']);
            }
        }

        // Ödeme durumunu ve kur bilgisini güncelle
        $stmt = $pdo->prepare("UPDATE payments SET 
            status = CASE WHEN status = 'paid' THEN 'pending' ELSE 'paid' END,
            exchange_rate = ?
            WHERE id = ? AND user_id = ?");

        if (!$stmt->execute([$exchange_rate, $_POST['id'], $user_id])) {
            throw new Exception(t('payment.update_error'));
            saveLog("Ödeme güncelleme hatası ($id): " . $e->getMessage(), 'error', 'markPaymentPaid', $_SESSION['user_id']);
        }

        $pdo->commit();
        saveLog("Ödeme güncellendi: " . $_POST['id'], 'info', 'markPaymentPaid', $_SESSION['user_id']);
        
        // Cache invalidation - ödeme durumu değişen ayın cache'ini temizle
        invalidateSummaryCacheForDate($user_id, $payment['first_date']);
        
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function updatePayment()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Gerekli alanları doğrula
    if (isset($_POST['id'])) {
        $id = validateRequired($_POST['id'] ?? null, t('payment.id'));
    }

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('payment.name'));
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, t('payment.amount'));
        $amount = validateNumeric($amount, t('payment.amount'));
        $amount = validateMinValue($amount, 0, t('payment.amount'));
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, t('payment.currency'));
        $currency = validateCurrency($currency, t('payment.currency'));
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, t('payment.date'));
        $first_date = validateDate($first_date, t('payment.date'));
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, t('payment.frequency'));
        $frequency = validateFrequency($frequency, t('payment.frequency'));
    }

    if (isset($_POST['card_id'])) {
        $card_id = validateRequired($_POST['card_id'] ?? null, t('payment.card'));
    }

    // Ödemenin mevcut olduğunu kontrol et
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$id, $user_id])) {
        throw new Exception(t('payment.not_found'));
        saveLog("Ödeme bulunamadı: " . $id, 'error', 'updatePayment', $_SESSION['user_id']);
    }
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception(t('payment.not_found'));
        saveLog("Ödeme bulunamadı: " . $id, 'error', 'updatePayment', $_SESSION['user_id']);
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini al
        $exchange_rate = null;
        if ($currency !== $base_currency) {
            // Kur güncellemesi isteniyorsa veya ilk defa kur ekleniyorsa
            if (isset($_POST['update_exchange_rate']) || $payment['exchange_rate'] === null) {
                $exchange_rate = getExchangeRate($currency, $base_currency);
                if (!$exchange_rate) {
                    throw new Exception(t('payment.rate_error'));
                    saveLog("Ödeme kuru hatası: " . $currency . " to " . $base_currency, 'error', 'updatePayment', $_SESSION['user_id']);
                }
            } else {
                // Mevcut kuru koru
                $exchange_rate = $payment['exchange_rate'];
            }
        }

        // get payment power from POST
        $payment_power = isset($_POST['update_payment_power']) && ($_POST['update_payment_power'] === 'on' || $_POST['update_payment_power'] === 'true') ? 1 : 0;

        // Ana kaydı güncelle
        $stmt = $pdo->prepare("UPDATE payments SET 
            name = ?, 
            amount = ?, 
            currency = ?, 
            first_date = ?, 
            frequency = ?,
            exchange_rate = ?,
            payment_power = ?,
            card_id = ?
            WHERE id = ? AND user_id = ?");

        if (!$stmt->execute([
            $name,
            $amount,
            $currency,
            $first_date,
            $frequency,
            $exchange_rate,
            $payment_power,
            $card_id,
            $id,
            $user_id
        ])) {
            throw new Exception(t('payment.update_error'));
            saveLog("Ödeme güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
        } else {
            saveLog("Ödeme güncellendi: $id", 'info', 'updatePayment', $_SESSION['user_id']);
        }

        // Child kayıtları güncelleme
        if (isset($_POST['update_children']) && ($_POST['update_children'] === 'true' || $_POST['update_children'] === 'on')) {
            // Parent ödeme ise, tüm child kayıtları güncelle
            if ($payment['parent_id'] === null) {
                $stmt = $pdo->prepare("UPDATE payments SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?,
                    payment_power = ?,
                    card_id = ?
                    WHERE parent_id = ? AND user_id = ?");

                if (!$stmt->execute([
                    $name,
                    $amount,
                    $currency,
                    $exchange_rate,
                    $payment_power,
                    $card_id,
                    $id,
                    $user_id
                ])) {
                    throw new Exception(t('payment.update_children_error'));
                    saveLog("Ödeme çocuk güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
                }

                saveLog("Ödeme çocuk güncellendi: $id", 'info', 'updatePayment', $_SESSION['user_id']);
            }
            // Child ödeme ise, kendisi ve sonraki kayıtları güncelle
            else {

                // eğer payment power önceki değerden farklı ise, tüm kayıtları güncelle
                if ($payment_power !== $payment['payment_power']) {

                    // ana (parent) kaydı güncelle
                    $stmt = $pdo->prepare("UPDATE payments SET 
                    payment_power = ?
                    WHERE id = ? AND user_id = ?");

                    if (!$stmt->execute([
                        $payment_power,
                        $payment['parent_id'],
                        $user_id
                    ])) {
                        throw new Exception(t('payment.update_children_error'));
                        saveLog("Parent payment power güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
                    }
                    saveLog("Parent payment power güncellendi: " . $payment['parent_id'], 'info', 'updatePayment', $_SESSION['user_id']);

                    // kendisini güncelle
                    $stmt = $pdo->prepare("UPDATE payments SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?,
                    payment_power = ?,
                    card_id = ?
                    WHERE id = ? AND user_id = ?");

                    if (!$stmt->execute([
                        $name,
                        $amount,
                        $currency,
                        $exchange_rate,
                        $payment_power,
                        $card_id,
                        $id,
                        $user_id
                    ])) {
                        throw new Exception(t('payment.update_children_error'));
                        saveLog("1 eğer payment power önceki değerden farklı ise, tüm kayıtları güncelle hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
                    }
                    saveLog("2 eğer payment power önceki değerden farklı ise, tüm kayıtları güncelle: $id", 'info', 'updatePayment', $_SESSION['user_id']);

                    // diğer child kayıtları güncelle
                    $stmt = $pdo->prepare("UPDATE payments SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?,
                    payment_power = ?,
                    card_id = ?
                    WHERE parent_id = ? AND id != ? AND user_id = ?");

                    if (!$stmt->execute([
                        $name,
                        $amount,
                        $currency,
                        $exchange_rate,
                        $payment_power,
                        $card_id,
                        $payment['parent_id'],
                        $id,
                        $user_id
                    ])) {
                        throw new Exception(t('payment.update_children_error'));
                        saveLog("3 eğer payment power önceki değerden farklı ise, tüm kayıtları güncelle hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
                    }
                    saveLog("4 eğer payment power önceki değerden farklı ise, tüm kayıtları güncelle: $id", 'info', 'updatePayment', $_SESSION['user_id']);
                }

                $stmt = $pdo->prepare("UPDATE payments SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?,
                    payment_power = ?,
                    card_id = ?
                    WHERE parent_id = ? AND first_date >= ? AND user_id = ?");

                if (!$stmt->execute([
                    $name,
                    $amount,
                    $currency,
                    $exchange_rate,
                    $payment_power,
                    $card_id,
                    $payment['parent_id'],
                    $first_date,
                    $user_id
                ])) {
                    throw new Exception(t('payment.update_children_error'));
                    saveLog("Ödeme çocuk güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updatePayment', $_SESSION['user_id']);
                }

                saveLog("Ödeme çocuk güncellendi: $id", 'info', 'updatePayment', $_SESSION['user_id']);
            }
        }

        $pdo->commit();
        
        // Cache invalidation - güncellenen ödemenin ayına ait cache'i temizle
        invalidateSummaryCacheForDate($user_id, $first_date);
        
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function getLastChildPaymentDate($payment_id)
{
    global $pdo, $user_id;

    // Ödeme bilgisini al
    $stmt = $pdo->prepare("SELECT parent_id FROM payments WHERE id = ? AND user_id = ?");
    $stmt->execute([$payment_id, $user_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception(t('payment.not_found'));
        saveLog("Ödeme bulunamadı: " . $payment_id, 'error', 'getLastChildPaymentDate', $_SESSION['user_id']);
    }

    $parent_id = $payment['parent_id'] ?? $payment_id; // Eğer parent_id null ise, kendisi parent'tır

    // Parent ID'ye sahip ödemenin son çocuk ödemesinin tarihini al
    $stmt = $pdo->prepare("SELECT MAX(first_date) as last_date
                          FROM payments
                          WHERE parent_id = ? AND user_id = ?");

    if (!$stmt->execute([$parent_id, $user_id])) {
        throw new Exception(t('payment.not_found'));
        saveLog("Ödeme bulunamadı: " . $parent_id, 'error', 'getLastChildPaymentDate', $_SESSION['user_id']);
    } else {
        saveLog("Ödeme bulundu: " . $parent_id, 'info', 'getLastChildPaymentDate', $_SESSION['user_id']);
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || !$result['last_date']) {
        // Eğer çocuk ödeme yoksa, parent'ın ilk tarihini döndür
        $stmt = $pdo->prepare("SELECT first_date FROM payments WHERE id = ? AND user_id = ?");
        $stmt->execute([$parent_id, $user_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            throw new Exception(t('payment.not_found'));
            saveLog("Ödeme bulunamadı: " . $parent_id, 'error', 'getLastChildPaymentDate', $_SESSION['user_id']);
        } else {
            saveLog("Ödeme bulundu: " . $parent_id, 'info', 'getLastChildPaymentDate', $_SESSION['user_id']);
        }

        return $parent['first_date'];
    }

    return $result['last_date'];
}
