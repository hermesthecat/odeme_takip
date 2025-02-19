<?php

require_once __DIR__ . '/../config.php';
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
        $name = validateRequired($_POST['name'] ?? null, "Ödeme adı");
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, "Tutar");
        $amount = validateNumeric($amount, "Tutar");
        $amount = validateMinValue($amount, 0, "Tutar");
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, "Para birimi");
        $currency = validateCurrency($currency, "Para birimi");
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, "Tarih");
        $first_date = validateDate($first_date, "Tarih");
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, "Tekrarlama sıklığı");
        $frequency = validateFrequency($frequency, "Tekrarlama sıklığı");
    } else {
        $frequency = 'none';
    }

    if (isset($_POST['end_date']) && $_POST['end_date'] !== '') {
        $end_date = validateDate($_POST['end_date'], "Bitiş tarihi");
        validateDateRange($first_date, $end_date);
    }

    $pdo->beginTransaction();

    // Kur bilgisini al
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception("Kur bilgisi alınamadı");
        }
    }

    // Ana kaydı ekle
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) 
                         VALUES (?, NULL, ?, ?, ?, ?, ?, ?)");

    if (!$stmt->execute([
        $user_id,
        $name,
        $amount,
        $currency,
        $first_date,
        $frequency,
        $exchange_rate
    ])) {
        throw new Exception("Ana kayıt eklenemedi");
    }

    $parent_id = $pdo->lastInsertId();

    // Tekrarlı kayıtlar için
    if ($frequency !== 'none' && isset($end_date) && $end_date > $first_date) {
        $month_interval = getMonthInterval($frequency);
        $total_months = getMonthDifference($first_date, $end_date);
        $repeat_count = floor($total_months / $month_interval);

        // Child kayıtları ekle
        if ($repeat_count > 0) {
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

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
                        $exchange_rate
                    ])) {
                        throw new Exception("Child kayıt eklenemedi");
                    }
                }
            }
        }
    }

    $pdo->commit();
    return true;
}

function deletePayment()
{
    global $pdo, $user_id;

    $pdo->beginTransaction();

    try {
        $id = $_POST['id'];
        $delete_children = isset($_POST['delete_children']) && $_POST['delete_children'] === 'true';

        if ($delete_children) {
            // Önce child kayıtları sil
            $stmt = $pdo->prepare("DELETE FROM payments WHERE (parent_id = ? OR id = ?) AND user_id = ?");
            $stmt->execute([$id, $id, $user_id]);
        } else {
            // Sadece seçili kaydı sil
            $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function loadPayments()
{
    global $pdo, $user_id, $month, $year;

    // Ödemeleri al
    $sql_payments = "SELECT p.*, 
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
                            WHERE p.user_id = ? 
                            AND MONTH(p.first_date) = ? 
                            AND YEAR(p.first_date) = ?
                            ORDER BY p.parent_id, p.first_date ASC";

    $stmt_payments = $pdo->prepare($sql_payments);
    $stmt_payments->execute([$user_id, $month, $year]);
    $payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);
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
                WHEN p2.currency = p1.currency THEN p2.amount 
                ELSE p2.amount * p2.exchange_rate 
            END)
            FROM payments p2 
            WHERE (p2.parent_id = p1.id OR p2.id = p1.id)
            AND p2.user_id = p1.user_id
        ) as yearly_total,
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
    ORDER BY yearly_total DESC";

    $stmt_recurring_payments = $pdo->prepare($sql_recurring_payments);
    $stmt_recurring_payments->execute([$user_id]);
    $recurring_payments = $stmt_recurring_payments->fetchAll(PDO::FETCH_ASSOC);
    return $recurring_payments;
}

function markPaymentPaid()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("UPDATE payments SET status = CASE WHEN status = 'paid' THEN 'pending' ELSE 'paid' END WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['id'], $user_id])) {
        return true;
    } else {
        return false;
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
        $id = validateRequired($_POST['id'] ?? null, "Ödeme ID");
    }

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, "Ödeme adı");
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, "Tutar");
        $amount = validateNumeric($amount, "Tutar");
        $amount = validateMinValue($amount, 0, "Tutar");
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, "Para birimi");
        $currency = validateCurrency($currency, "Para birimi");
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, "Tarih");
        $first_date = validateDate($first_date, "Tarih");
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, "Tekrarlama sıklığı");
        $frequency = validateFrequency($frequency, "Tekrarlama sıklığı");
    }

    // Ödemenin mevcut olduğunu kontrol et
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception("Ödeme bulunamadı");
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini al
        $exchange_rate = null;
        if ($currency !== $base_currency) {
            $exchange_rate = getExchangeRate($currency, $base_currency);
            if (!$exchange_rate) {
                throw new Exception("Kur bilgisi alınamadı");
            }
        }

        // Ana kaydı güncelle
        $stmt = $pdo->prepare("UPDATE payments SET 
            name = ?, 
            amount = ?, 
            currency = ?, 
            first_date = ?, 
            frequency = ?,
            exchange_rate = ?
            WHERE id = ? AND user_id = ?");

        if (!$stmt->execute([
            $name,
            $amount,
            $currency,
            $first_date,
            $frequency,
            $exchange_rate,
            $id,
            $user_id
        ])) {
            throw new Exception("Ödeme güncellenemedi");
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
