<?php
require_once 'config.php';
checkLogin();

// Tekrar sayısını hesaplama fonksiyonu
function calculateRepeatCount($frequency) {
    switch($frequency) {
        case 'none': return 1;
        case 'monthly': return 1;
        case 'bimonthly': return 2;
        case 'quarterly': return 3;
        case 'fourmonthly': return 4;
        case 'fivemonthly': return 5;
        case 'sixmonthly': return 6;
        case 'yearly': return 12;
        default: return 1;
    }
}

// İki tarih arasındaki ay sayısını hesaplama
function getMonthDifference($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    $interval = $start->diff($end);
    return $interval->y * 12 + $interval->m;
}

// Tekrarlama sıklığına göre ay aralığını hesapla
function getMonthInterval($frequency) {
    switch($frequency) {
        case 'monthly': return 1;
        case 'bimonthly': return 2;
        case 'quarterly': return 3;
        case 'fourmonthly': return 4;
        case 'fivemonthly': return 5;
        case 'sixmonthly': return 6;
        case 'yearly': return 12;
        default: return 0;
    }
}

// Sonraki ödeme tarihini hesapla
function calculateNextPaymentDate($date, $months) {
    $next_date = new DateTime($date);
    $next_date->modify('+' . $months . ' months');
    return $next_date->format('Y-m-d');
}

// Sonraki tarihi hesaplama fonksiyonu
function calculateNextDate($date, $frequency, $count = 1) {
    $nextDate = new DateTime($date);
    
    switch($frequency) {
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
                $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, NULL, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt->execute([
                    $user_id,
                    $_POST['name'],
                    $_POST['amount'],
                    $_POST['currency'],
                    $first_date,
                    $frequency,
                    $first_date
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
                        $stmt = $pdo->prepare("INSERT INTO income (user_id, parent_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        
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
                                    $frequency,
                                    $income_date
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
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, NULL, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt->execute([
                    $user_id,
                    $_POST['name'],
                    $_POST['amount'],
                    $_POST['currency'],
                    $first_date,
                    $frequency,
                    $first_date
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
                        $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        
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
                                    $frequency,
                                    $payment_date
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
            
            // Ödemeleri al (hem ana hem child kayıtlar)
            $sql = "SELECT p.*, parent.id as parent_payment_id, parent.name as parent_name 
                   FROM payments p 
                   LEFT JOIN payments parent ON p.parent_id = parent.id 
                   WHERE p.user_id = ? AND MONTH(p.first_date) = ? AND YEAR(p.first_date) = ?
                   ORDER BY p.first_date ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $month, $year]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Gelirleri al
            $sql = "SELECT * FROM income WHERE user_id = ? AND (
                (frequency = 'none' AND MONTH(first_date) = ? AND YEAR(first_date) = ?) OR
                (frequency != 'none' AND MONTH(next_date) = ? AND YEAR(next_date) = ?)
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $month, $year, $month, $year]);
            $incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Birikimleri al
            $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => [
                    'incomes' => $incomes,
                    'savings' => $savings,
                    'payments' => $payments
                ]
            ];
            break;

        case 'mark_payment_paid':
            $stmt = $pdo->prepare("UPDATE payments SET status = 'paid' WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$_POST['id'], $user_id])) {
                $response = ['status' => 'success', 'message' => 'Ödeme durumu güncellendi'];
            }
            break;

        case 'mark_income_received':
            $id = $_POST['id'];
            $userId = $_SESSION['user_id'];
            
            $stmt = $pdo->prepare("UPDATE income SET status = 'received' WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            
            if ($stmt->execute()) {
                $response = array('status' => 'success');
            } else {
                $response = array('status' => 'error', 'message' => 'Gelir durumu güncellenirken bir hata oluştu.');
            }
            break;
    }
}

echo json_encode($response); 