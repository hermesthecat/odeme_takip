<?php
require_once 'config.php';
checkLogin();

// Tekrar sayısını hesaplama fonksiyonu
function calculateRepeatCount($frequency)
{
    switch ($frequency) {
        case 'none':
            return 1;
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 1;
    }
}

// İki tarih arasındaki ay sayısını hesaplama
function getMonthDifference($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    $interval = $start->diff($end);
    return $interval->y * 12 + $interval->m;
}

// Tekrarlama sıklığına göre ay aralığını hesapla
function getMonthInterval($frequency)
{
    switch ($frequency) {
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 0;
    }
}

// Sonraki ödeme tarihini hesapla
function calculateNextPaymentDate($date, $months)
{
    $next_date = new DateTime($date);
    $next_date->modify('+' . $months . ' months');
    return $next_date->format('Y-m-d');
}

// Sonraki tarihi hesaplama fonksiyonu
function calculateNextDate($date, $frequency, $count = 1)
{
    $nextDate = new DateTime($date);

    switch ($frequency) {
        case 'monthly':
            $nextDate->modify('+' . $count . ' month');
            break;
        case 'bimonthly':
            $nextDate->modify('+' . ($count * 2) . ' months');
            break;
        case 'quarterly':
            $nextDate->modify('+' . ($count * 3) . ' months');
            break;
        case 'fourmonthly':
            $nextDate->modify('+' . ($count * 4) . ' months');
            break;
        case 'fivemonthly':
            $nextDate->modify('+' . ($count * 5) . ' months');
            break;
        case 'sixmonthly':
            $nextDate->modify('+' . ($count * 6) . ' months');
            break;
        case 'yearly':
            $nextDate->modify('+' . $count . ' year');
            break;
        default:
            return $date;
    }

    return $nextDate->format('Y-m-d');
}

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'add_income':
            try {
                $pdo->beginTransaction();

                $frequency = $_POST['frequency'];
                $first_date = $_POST['first_date'];
                $end_date = $_POST['end_date'] ?? $first_date;

                // Ana kaydı ekle
                $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency) VALUES (?, NULL, ?, ?, ?, ?, ?)");

                if (!$stmt->execute([
                    $user_id,
                    $_POST['name'],
                    $_POST['amount'],
                    $_POST['currency'],
                    $first_date,
                    $frequency
                ])) {
                    throw new Exception("Ana kayıt eklenemedi");
                }

                $parent_id = $pdo->lastInsertId();

                // Tekrarlı kayıtlar için
                if ($frequency !== 'none' && $end_date > $first_date) {
                    $month_interval = getMonthInterval($frequency);
                    $total_months = getMonthDifference($first_date, $end_date);
                    $repeat_count = floor($total_months / $month_interval);

                    // Child kayıtları ekle
                    if ($repeat_count > 0) {
                        $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency) VALUES (?, ?, ?, ?, ?, ?, ?)");

                        for ($i = 1; $i <= $repeat_count; $i++) {
                            $income_date = calculateNextPaymentDate($first_date, $i * $month_interval);

                            // Bitiş tarihini geçmemesi için kontrol
                            if ($income_date <= $end_date) {
                                if (!$stmt->execute([
                                    $user_id,
                                    $parent_id,
                                    $_POST['name'],
                                    $_POST['amount'],
                                    $_POST['currency'],
                                    $income_date,
                                    $frequency
                                ])) {
                                    throw new Exception("Child kayıt eklenemedi");
                                }
                            }
                        }
                    }
                }

                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Gelir başarıyla eklendi'];
            } catch (Exception $e) {
                $pdo->rollBack();
                $response = ['status' => 'error', 'message' => 'Gelir eklenirken hata: ' . $e->getMessage()];
            }
            break;

        case 'add_saving':
            $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, target_amount, currency, start_date, target_date) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $_POST['name'], $_POST['target_amount'], $_POST['currency'], $_POST['start_date'], $_POST['target_date']])) {
                $response = ['status' => 'success', 'message' => 'Birikim başarıyla eklendi'];
            }
            break;

        case 'add_payment':
            try {
                $pdo->beginTransaction();

                $frequency = $_POST['frequency'];
                $first_date = $_POST['first_date'];
                $end_date = $_POST['end_date'] ?? $first_date;

                // Ana kaydı ekle
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency) VALUES (?, NULL, ?, ?, ?, ?, ?)");

                if (!$stmt->execute([
                    $user_id,
                    $_POST['name'],
                    $_POST['amount'],
                    $_POST['currency'],
                    $first_date,
                    $frequency
                ])) {
                    throw new Exception("Ana kayıt eklenemedi");
                }

                $parent_id = $pdo->lastInsertId();

                // Tekrarlı kayıtlar için
                if ($frequency !== 'none' && $end_date > $first_date) {
                    $month_interval = getMonthInterval($frequency);
                    $total_months = getMonthDifference($first_date, $end_date);
                    $repeat_count = floor($total_months / $month_interval);

                    // Child kayıtları ekle
                    if ($repeat_count > 0) {
                        $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency) VALUES (?, ?, ?, ?, ?, ?, ?)");

                        for ($i = 1; $i <= $repeat_count; $i++) {
                            $payment_date = calculateNextPaymentDate($first_date, $i * $month_interval);

                            // Bitiş tarihini geçmemesi için kontrol
                            if ($payment_date <= $end_date) {
                                if (!$stmt->execute([
                                    $user_id,
                                    $parent_id,
                                    $_POST['name'],
                                    $_POST['amount'],
                                    $_POST['currency'],
                                    $payment_date,
                                    $frequency
                                ])) {
                                    throw new Exception("Child kayıt eklenemedi");
                                }
                            }
                        }
                    }
                }

                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Ödeme başarıyla eklendi'];
            } catch (Exception $e) {
                $pdo->rollBack();
                $response = ['status' => 'error', 'message' => 'Ödeme eklenirken hata: ' . $e->getMessage()];
            }
            break;

        case 'delete_income':
            $stmt = $pdo->prepare("DELETE FROM income WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Gelir başarıyla silindi'];
            }
            break;

        case 'delete_saving':
            $stmt = $pdo->prepare("DELETE FROM savings WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Birikim başarıyla silindi'];
            }
            break;

        case 'delete_payment':
            $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Ödeme başarıyla silindi'];
            }
            break;

        case 'get_data':
            $month = intval($_POST['month']) + 1;
            $year = intval($_POST['year']);

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

            // Tekrarlayan ödemeleri al
            $sql_recurring_payments = "SELECT 
                        p1.name,
                        p1.amount,
                        p1.currency,
                        p1.frequency,
                        CASE 
                            WHEN p1.frequency = 'monthly' THEN 12
                            WHEN p1.frequency = 'bimonthly' THEN 6
                            WHEN p1.frequency = 'quarterly' THEN 4
                            WHEN p1.frequency = 'fourmonthly' THEN 3
                            WHEN p1.frequency = 'fivemonthly' THEN 2.4
                            WHEN p1.frequency = 'sixmonthly' THEN 2
                            WHEN p1.frequency = 'yearly' THEN 1
                            ELSE 1
                        END as yearly_repeat_count,
                        CASE 
                            WHEN p1.frequency = 'monthly' THEN p1.amount * 12
                            WHEN p1.frequency = 'bimonthly' THEN p1.amount * 6
                            WHEN p1.frequency = 'quarterly' THEN p1.amount * 4
                            WHEN p1.frequency = 'fourmonthly' THEN p1.amount * 3
                            WHEN p1.frequency = 'fivemonthly' THEN p1.amount * 2.4
                            WHEN p1.frequency = 'sixmonthly' THEN p1.amount * 2
                            WHEN p1.frequency = 'yearly' THEN p1.amount
                            ELSE p1.amount
                        END as yearly_total,
                        CONCAT(
                            (SELECT COUNT(*) FROM payments p2 
                             WHERE p2.parent_id = p1.id 
                             AND p2.status = 'paid'
                             AND p2.user_id = p1.user_id),
                            '/',
                            (SELECT COUNT(*) FROM payments p3 
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

            // Birikimleri al
            $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'status' => 'success',
                'data' => [
                    'incomes' => $incomes,
                    'savings' => $savings,
                    'payments' => $payments,
                    'recurring_payments' => $recurring_payments
                ]
            ];
            break;

        case 'mark_payment_paid':
            $stmt = $pdo->prepare("UPDATE payments SET status = CASE WHEN status = 'paid' THEN 'pending' ELSE 'paid' END WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Ödeme durumu güncellendi'];
            }
            break;

        case 'mark_income_received':
            $stmt = $pdo->prepare("UPDATE income SET status = CASE WHEN status = 'received' THEN 'pending' ELSE 'received' END WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Gelir durumu güncellendi'];
            }
            break;

        case 'update_saving':
            $stmt = $pdo->prepare("UPDATE savings SET current_amount = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['current_amount'], $_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Birikim güncellendi'];
            }
            break;

        case 'update_full_saving':
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
                $response = ['status' => 'success', 'message' => 'Birikim başarıyla güncellendi'];
            }
            break;

        case 'transfer_unpaid_payments':
            try {
                $pdo->beginTransaction();

                // Mevcut ayın ödenmemiş ödemelerini al (hem ana hem de tekrarlanan ödemeler)
                $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND MONTH(first_date) = ? AND YEAR(first_date) = ? AND status = 'pending'");
                $stmt->execute([$user_id, $_POST['current_month'], $_POST['current_year']]);
                $unpaid_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Ay ve yıl hesaplama
                $next_month = $_POST['current_month'] == 12 ? 1 : $_POST['current_month'] + 1;
                $next_year = $_POST['current_month'] == 12 ? $_POST['current_year'] + 1 : $_POST['current_year'];
                $current_month_name = date('F', mktime(0, 0, 0, $_POST['current_month'], 1));

                foreach ($unpaid_payments as $payment) {
                    // Yeni ödeme tarihi
                    $new_date = date('Y-m-d', mktime(0, 0, 0, $next_month, date('d', strtotime($payment['first_date'])), $next_year));

                    // Eğer tekrarlanan bir ödeme ise
                    if ($payment['frequency'] !== 'none') {
                        // Ana kayıt ise
                        if ($payment['parent_id'] === null) {
                            // Yeni ana kaydı ekle
                            $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency) 
                                                 VALUES (?, NULL, ?, ?, ?, ?, ?)");
                            $new_name = $payment['name'] . ' (' . $current_month_name . ' Ayından Aktarıldı)';

                            if (!$stmt->execute([
                                $user_id,
                                $new_name,
                                $payment['amount'],
                                $payment['currency'],
                                $new_date,
                                $payment['frequency']
                            ])) {
                                throw new Exception("Yeni ana kayıt eklenemedi");
                            }

                            $new_parent_id = $pdo->lastInsertId();

                            // Child kayıtları da aktar
                            $stmt = $pdo->prepare("SELECT * FROM payments WHERE parent_id = ? AND first_date > ?");
                            $stmt->execute([$payment['id'], $payment['first_date']]);
                            $child_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($child_payments as $child) {
                                $child_new_date = date('Y-m-d', strtotime($child['first_date'] . ' +1 month'));
                                $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency) 
                                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                                if (!$stmt->execute([
                                    $user_id,
                                    $new_parent_id,
                                    $new_name,
                                    $child['amount'],
                                    $child['currency'],
                                    $child_new_date,
                                    $child['frequency']
                                ])) {
                                    throw new Exception("Child kayıt eklenemedi");
                                }
                            }
                        }
                    } else {
                        // Tekrarlanmayan ödeme ise
                        $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency) 
                                             VALUES (?, ?, ?, ?, ?, ?, 'none')");
                        $new_name = $payment['name'] . ' (' . $current_month_name . ' Ayından Aktarıldı)';

                        if (!$stmt->execute([
                            $user_id,
                            $payment['parent_id'],
                            $new_name,
                            $payment['amount'],
                            $payment['currency'],
                            $new_date
                        ])) {
                            throw new Exception("Yeni ödeme eklenemedi");
                        }
                    }

                    // Eski ödemeyi sil
                    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
                    if (!$stmt->execute([$payment['id'], $user_id])) {
                        throw new Exception("Eski ödeme silinemedi");
                    }
                }

                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Ödemeler sonraki aya aktarıldı'];
            } catch (Exception $e) {
                $pdo->rollBack();
                $response = ['status' => 'error', 'message' => 'Ödemeler aktarılırken hata: ' . $e->getMessage()];
            }
            break;
    }
}

echo json_encode($response);
