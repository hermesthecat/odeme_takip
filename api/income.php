<?php

/**
 * Gelir yönetimi API'si
 * Income management API
 * 
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/../config.php';
checkLogin();

/**
 * Yeni gelir ekler
 * Adds new income
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function addIncome()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    // Get user's base currency
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
    // Get exchange rate
    $exchange_rate = null;
    if ($currency !== $base_currency) {
        $exchange_rate = getExchangeRate($currency, $base_currency);
        if (!$exchange_rate) {
            throw new Exception(t('income.rate_error'));
        }
    }

    // Ana kaydı ekle
    // Add main record
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
        saveLog("Gelir ekleme hatası ($name): " . $e->getMessage(), 'error', 'addIncome', $_SESSION['user_id']);
        // Income addition error
    }

    $parent_id = $pdo->lastInsertId();

    // Tekrarlı kayıtlar için
    // For recurring records
    if ($frequency !== 'none' && isset($end_date) && $end_date > $first_date) {
        $month_interval = getMonthInterval($frequency);
        $total_months = getMonthDifference($first_date, $end_date);
        $repeat_count = floor($total_months / $month_interval);

        // Child kayıtları ekle
        // Add child records
        if ($repeat_count > 0) {
            $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 1; $i <= $repeat_count; $i++) {
                $income_date = calculateNextPaymentDate($first_date, $i * $month_interval);

                // Bitiş tarihini geçmemesi için kontrol
                // Check not to exceed end date
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
                        saveLog("Tekrarlı gelir ekleme hatası ($name): " . $e->getMessage(), 'error', 'addIncome', $_SESSION['user_id']);
                        // Recurring income addition error
                    }
                }
            }
        }
    }

    $pdo->commit();
    
    // Cache invalidation - gelir eklenen ay cache'ini temizle
    invalidateSummaryCacheForDate($user_id, $first_date);
    
    return true;
}

/**
 * Gelir kaydını siler
 * Deletes income record
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function deleteIncome()
{
    global $pdo, $user_id;

    $id = $_POST['id'];
    
    // Silmeden önce tarihi al - cache invalidation için
    $date_stmt = $pdo->prepare("SELECT first_date FROM income WHERE id = ? AND user_id = ?");
    $date_stmt->execute([$id, $user_id]);
    $income_record = $date_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$income_record) {
        throw new Exception(t('income.not_found'));
    }

    $stmt = $pdo->prepare("DELETE FROM income WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $user_id])) {
        // Cache invalidation - silinen gelirin ayına ait cache'i temizle
        invalidateSummaryCacheForDate($user_id, $income_record['first_date']);
        
        return true;
    } else {
        throw new Exception(t('income.delete_error'));
        saveLog("Gelir silme hatası ($id): " . $e->getMessage(), 'error', 'deleteIncome', $_SESSION['user_id']);
        // Income deletion error
    }
}

/**
 * Gelirleri listeler
 * Lists incomes
 * 
 * @return array - Gelir listesi / Income list
 */
function loadIncomes()
{
    global $pdo, $user_id, $month, $year;

    // Gelirleri al
    // Get incomes
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
            ORDER BY i.name ASC";

    $stmt_incomes = $pdo->prepare($sql_incomes);
    $stmt_incomes->execute([$user_id, $month, $year]);
    $incomes = $stmt_incomes->fetchAll(PDO::FETCH_ASSOC);

    saveLog("Gelirler alındı: " . $user_id . " " . $month . " " . $year, 'info', 'loadIncomes', $_SESSION['user_id']);
    // Incomes retrieved

    // Tarihleri formatla
    // Format dates
    foreach ($incomes as &$income) {
        $income['formatted_first_date'] = formatDate($income['first_date']);
        $income['formatted_next_income_date'] = $income['next_income_date'] ? formatDate($income['next_income_date']) : null;
    }
    unset($income); // Referansı temizle / Clear reference

    return $incomes;
}

/**
 * Geliri alındı/alınmadı olarak işaretler
 * Marks income as received/not received
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function markIncomeReceived()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    // Get user's base currency
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Gelir bilgilerini al
    // Get income information
    $stmt = $pdo->prepare("SELECT * FROM income WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);
    $income = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$income) {
        throw new Exception(t('income.not_found'));
        saveLog("Gelir bulunamadı: " . $_POST['id'], 'error', 'markIncomeReceived', $_SESSION['user_id']);
        // Income not found
    }

    $pdo->beginTransaction();

    try {
        // Kur bilgisini güncelle
        // Update exchange rate
        $exchange_rate = null;
        if ($income['currency'] !== $base_currency) {
            $exchange_rate = getExchangeRate($income['currency'], $base_currency);
            if (!$exchange_rate) {
                throw new Exception(t('income.rate_error'));
                saveLog("Gelir kuru hatası: " . $_POST['id'], 'error', 'markIncomeReceived', $_SESSION['user_id']);
                // Income exchange rate error
            }
        }

        // Gelir durumunu ve kur bilgisini güncelle
        // Update income status and exchange rate
        $stmt = $pdo->prepare("UPDATE income SET 
            status = CASE WHEN status = 'received' THEN 'pending' ELSE 'received' END,
            exchange_rate = ?
            WHERE id = ? AND user_id = ?");

        $id = $_POST['id']; // Gelir ID / Income ID

        if (!$stmt->execute([$exchange_rate, $id, $user_id])) {
            throw new Exception(t('income.update_error'));
            saveLog("Gelir güncelleme hatası ($id): " . $e->getMessage(), 'error', 'markIncomeReceived', $_SESSION['user_id']);
            // Income update error
        } else {
            saveLog("Gelir güncellendi: $id", 'info', 'markIncomeReceived', $_SESSION['user_id']);
            // Income updated
        }

        $pdo->commit();
        
        // Cache invalidation - gelir durumu değişen ayın cache'ini temizle
        invalidateSummaryCacheForDate($user_id, $income['first_date']);
        
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Gelir kaydını günceller
 * Updates income record
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function updateIncome()
{
    global $pdo, $user_id;

    // Kullanıcının ana para birimini al
    // Get user's base currency
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Gerekli alanları doğrula
    // Validate required fields
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
        saveLog("Gelir bulunamadı: " . $id, 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
    }
    $income = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$income) {
        throw new Exception(t('income.not_found'));
        saveLog("Gelir bulunamadı: " . $id, 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
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
                    saveLog("Gelir kuru hatası: " . $id, 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
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
            saveLog("Gelir güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
        } else {
            saveLog("Gelir güncellendi: $id", 'info', 'updateIncome', $_SESSION['user_id']);
        }

        // Child kayıtları güncelleme
        if (isset($_POST['update_children']) && ($_POST['update_children'] === 'true' || $_POST['update_children'] === 'on')) {
            // Parent gelir ise, tüm child kayıtları güncelle
            if ($income['parent_id'] === null) {
                $stmt = $pdo->prepare("UPDATE income SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?
                    WHERE parent_id = ? AND user_id = ?");

                if (!$stmt->execute([
                    $name,
                    $amount,
                    $currency,
                    $exchange_rate,
                    $id,
                    $user_id
                ])) {
                    throw new Exception(t('income.update_children_error'));
                    saveLog("Gelir çocuk güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
                }

                saveLog("Gelir çocuk güncellendi: $id", 'info', 'updateIncome', $_SESSION['user_id']);
            }
            // Child gelir ise, kendisi ve sonraki kayıtları güncelle
            else {
                $stmt = $pdo->prepare("UPDATE income SET 
                    name = ?,
                    amount = ?, 
                    currency = ?, 
                    exchange_rate = ?
                    WHERE parent_id = ? AND first_date >= ? AND user_id = ?");

                if (!$stmt->execute([
                    $name,
                    $amount,
                    $currency,
                    $exchange_rate,
                    $income['parent_id'],
                    $first_date,
                    $user_id
                ])) {
                    throw new Exception(t('income.update_children_error'));
                    saveLog("Gelir çocuk güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updateIncome', $_SESSION['user_id']); // Gelir ID
                }

                saveLog("Gelir çocuk güncellendi: $id", 'info', 'updateIncome', $_SESSION['user_id']);
            }
        }

        $pdo->commit();
        
        // Cache invalidation - güncellenen gelirin ayına ait cache'i temizle
        invalidateSummaryCacheForDate($user_id, $first_date);
        
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function getLastChildIncomeDate($income_id)
{
    global $pdo, $user_id;

    // Gelir bilgisini al
    // Get income information
    $stmt = $pdo->prepare("SELECT parent_id FROM income WHERE id = ? AND user_id = ?");
    $stmt->execute([$income_id, $user_id]);
    $income = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$income) {
        throw new Exception(t('income.not_found'));
        saveLog("Gelir bulunamadı: " . $income_id, 'error', 'getLastChildIncomeDate', $_SESSION['user_id']);
        // Income not found
    }

    // Eğer parent_id null ise, kendisi parent'tır
    // If parent_id is null, it is the parent itself
    $parent_id = $income['parent_id'] ?? $income_id;

    // Parent ID'ye sahip gelirin son çocuk gelirinin tarihini al
    // Get the date of the last child income with the parent ID
    $stmt = $pdo->prepare("SELECT MAX(first_date) as last_date
                          FROM income
                          WHERE parent_id = ? AND user_id = ?");

    if (!$stmt->execute([$parent_id, $user_id])) {
        throw new Exception(t('income.not_found'));
        saveLog("Gelir bulunamadı: " . $parent_id, 'error', 'getLastChildIncomeDate', $_SESSION['user_id']);
        // Income not found
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || !$result['last_date']) {
        // Eğer çocuk gelir yoksa, parent'ın ilk tarihini döndür
        // If there is no child income, return the parent's first date
        $stmt = $pdo->prepare("SELECT first_date FROM income WHERE id = ? AND user_id = ?");
        $stmt->execute([$parent_id, $user_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            throw new Exception(t('income.not_found'));
            saveLog("Gelir bulunamadı: " . $parent_id, 'error', 'getLastChildIncomeDate', $_SESSION['user_id']);
            // Income not found
        }

        return $parent['first_date'];
    }

    return $result['last_date'];
}
