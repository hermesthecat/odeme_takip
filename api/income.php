<?php

require_once __DIR__ . '/../config.php';
checkLogin();

function addIncome()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('income.name'));
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, t('income.amount'));
        $amount = validateNumeric($amount, t('income.amount'));
        $amount = validateMinValue($amount, 0, t('income.amount'));
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, t('income.currency'));
        $currency = validateCurrency($currency, t('income.currency'));
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, t('income.date'));
        $first_date = validateDate($first_date, t('income.date'));
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, t('income.frequency'));
        $frequency = validateFrequency($frequency, t('income.frequency'));
    } else {
        $frequency = 'none';
    }

    if (isset($_POST['end_date']) && $_POST['end_date'] !== '') {
        $end_date = validateDate($_POST['end_date'], t('income.end_date'));
        validateDateRange($first_date, $end_date);
    }

    $pdo->beginTransaction();

    // Kur bilgisini al
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception(t('income.rate_error'));
        }
    }

    // Ana kaydı ekle
    $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) 
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
        throw new Exception(t('income.add_error'));
    }

    $parent_id = $pdo->lastInsertId();

    // Tekrarlı kayıtlar için
    if ($frequency !== 'none' && isset($end_date) && $end_date > $first_date) {
        $month_interval = getMonthInterval($frequency);
        $total_months = getMonthDifference($first_date, $end_date);
        $repeat_count = floor($total_months / $month_interval);

        // Child kayıtları ekle
        if ($repeat_count > 0) {
            $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 1; $i <= $repeat_count; $i++) {
                $income_date = calculateNextPaymentDate($first_date, $i * $month_interval);

                // Bitiş tarihini geçmemesi için kontrol
                if ($income_date <= $end_date) {
                    if (!$stmt->execute([
                        $user_id,
                        $parent_id,
                        $name,
                        $amount,
                        $currency,
                        $income_date,
                        $frequency,
                        $exchange_rate
                    ])) {
                        throw new Exception(t('income.add_recurring_error'));
                    }
                }
            }
        }
    }

    $pdo->commit();
    return true;
}

function deleteIncome()
{
    global $pdo, $user_id;

    $stmt = $pdo->prepare("DELETE FROM income WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['id'], $user_id])) {
        return true;
    } else {
        throw new Exception(t('income.delete_error'));
    }
}

function loadIncomes()
{
    global $pdo, $user_id, $month, $year;

    // Gelirleri al
    $sql_incomes = "SELECT i.*, 
            CASE 
                WHEN i.parent_id IS NULL THEN (
                    SELECT MIN(i2.first_date)
                    FROM income i2
                    WHERE i2.parent_id = i.id
                    AND i2.user_id = i.user_id
                )
                ELSE (
                    SELECT MIN(i2.first_date)
                    FROM income i2
                    WHERE i2.parent_id = i.parent_id
                    AND i2.first_date > i.first_date
                    AND i2.user_id = i.user_id
                )
            END as next_income_date
            FROM income i 
            WHERE i.user_id = ? 
            AND MONTH(i.first_date) = ? 
            AND YEAR(i.first_date) = ?
            ORDER BY i.parent_id, i.first_date ASC";

    $stmt_incomes = $pdo->prepare($sql_incomes);
    $stmt_incomes->execute([$user_id, $month, $year]);
    $incomes = $stmt_incomes->fetchAll(PDO::FETCH_ASSOC);

    return $incomes;
}

function markIncomeReceived()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Gelir bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM income WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);
    $income = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$income) {
        throw new Exception(t('income.not_found'));
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini güncelle
        $exchange_rate = null;
        if ($income['currency'] !== $base_currency) {
            $exchange_rate = getExchangeRate($income['currency'], $base_currency);
            if (!$exchange_rate) {
                throw new Exception(t('income.rate_error'));
            }
        }

        // Gelir durumunu ve kur bilgisini güncelle
        $stmt = $pdo->prepare("UPDATE income SET 
            status = CASE WHEN status = 'received' THEN 'pending' ELSE 'received' END,
            exchange_rate = ?
            WHERE id = ? AND user_id = ?");

        if (!$stmt->execute([$exchange_rate, $_POST['id'], $user_id])) {
            throw new Exception(t('income.update_error'));
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateIncome()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Gerekli alanları doğrula
    if (isset($_POST['id'])) {
        $id = validateRequired($_POST['id'] ?? null, t('income.id'));
    }

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('income.name'));
    }

    if (isset($_POST['amount'])) {
        $amount = validateRequired($_POST['amount'] ?? null, t('income.amount'));
        $amount = validateNumeric($amount, t('income.amount'));
        $amount = validateMinValue($amount, 0, t('income.amount'));
    }

    if (isset($_POST['currency'])) {
        $currency = validateRequired($_POST['currency'] ?? null, t('income.currency'));
        $currency = validateCurrency($currency, t('income.currency'));
    }

    if (isset($_POST['first_date'])) {
        $first_date = validateRequired($_POST['first_date'] ?? null, t('income.date'));
        $first_date = validateDate($first_date, t('income.date'));
    }

    if (isset($_POST['frequency'])) {
        $frequency = validateRequired($_POST['frequency'] ?? null, t('income.frequency'));
        $frequency = validateFrequency($frequency, t('income.frequency'));
    }

    // Gelirin mevcut olduğunu kontrol et
    $stmt = $pdo->prepare("SELECT * FROM income WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$id, $user_id])) {
        throw new Exception(t('income.not_found'));
    }
    $income = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$income) {
        throw new Exception(t('income.not_found'));
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini al
        $exchange_rate = null;
        if ($currency !== $base_currency) {
            // Kur güncellemesi isteniyorsa veya ilk defa kur ekleniyorsa
            if (isset($_POST['update_exchange_rate']) || $income['exchange_rate'] === null) {
                $exchange_rate = getExchangeRate($currency, $base_currency);
                if (!$exchange_rate) {
                    throw new Exception(t('income.rate_error'));
                }
            } else {
                // Mevcut kuru koru
                $exchange_rate = $income['exchange_rate'];
            }
        }

        // Ana kaydı güncelle
        $stmt = $pdo->prepare("UPDATE income SET 
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
            throw new Exception(t('income.update_error'));
        }

        // Child kayıtları güncelleme
        if (isset($_POST['update_children']) && $_POST['update_children'] === 'true') {
            // Parent gelir ise, tüm child kayıtları güncelle
            if ($income['parent_id'] === null) {
                $stmt = $pdo->prepare("UPDATE income SET 
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?
                    WHERE parent_id = ? AND user_id = ?");
                
                if (!$stmt->execute([
                    $amount,
                    $currency,
                    $exchange_rate,
                    $id,
                    $user_id
                ])) {
                    throw new Exception(t('income.update_children_error'));
                }
            } 
            // Child gelir ise, kendisi ve sonraki kayıtları güncelle
            else {
                $stmt = $pdo->prepare("UPDATE income SET 
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?
                    WHERE parent_id = ? AND first_date >= ? AND user_id = ?");
                
                if (!$stmt->execute([
                    $amount,
                    $currency,
                    $exchange_rate,
                    $income['parent_id'],
                    $first_date,
                    $user_id
                ])) {
                    throw new Exception(t('income.update_children_error'));
                }
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getLastChildIncomeDate($parent_id)
{
    global $pdo, $user_id;

    // Parent ID'ye sahip gelirin son çocuk gelirinin tarihini al
    $stmt = $pdo->prepare("SELECT MAX(first_date) as last_date 
                          FROM income 
                          WHERE parent_id = ? AND user_id = ?");
    
    if (!$stmt->execute([$parent_id, $user_id])) {
        throw new Exception(t('income.not_found'));
    }
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || !$result['last_date']) {
        throw new Exception(t('income.not_found'));
    }
    
    return $result['last_date'];
}
