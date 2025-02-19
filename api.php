<?php
require_once __DIR__ . './api/utils.php';
require_once __DIR__ . './api/validate.php';
require_once __DIR__ . './api/currency.php';
require_once __DIR__ . './api/xss.php';


require_once __DIR__ . '/api/income.php';
require_once __DIR__ . '/api/savings.php';
require_once __DIR__ . '/api/payments.php';
require_once __DIR__ . '/api/summary.php';
require_once __DIR__ . '/api/user.php';
require_once __DIR__ . '/api/transfer.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'add_income':
            try {
                if (addIncome()) {
                    $response = ['status' => 'success', 'message' => 'Gelir başarıyla eklendi'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Gelir eklenemedi'];
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'add_saving':
            try {

                if (addSaving()) {
                    $response = ['status' => 'success', 'message' => 'Birikim başarıyla eklendi'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Birikim eklenemedi'];
                }
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'add_payment':
            try {

                if (addPayment()) {
                    $response = ['status' => 'success', 'message' => 'Ödeme başarıyla eklendi'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Ödeme eklenemedi'];
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'delete_income':
            if (deleteIncome()) {
                $response = ['status' => 'success', 'message' => 'Gelir başarıyla silindi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Gelir silinemedi'];
            }
            break;

        case 'delete_saving':
            if (deleteSaving()) {
                $response = ['status' => 'success', 'message' => 'Birikim başarıyla silindi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Birikim silinemedi'];
            }
            break;

        case 'delete_payment':
            if (deletePayment()) {
                $response = ['status' => 'success', 'message' => 'Ödeme başarıyla silindi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Ödeme silinemedi'];
            }
            break;

        case 'get_data':
            $month = intval($_POST['month']) + 1;
            $year = intval($_POST['year']);
            $load_type = $_POST['load_type'] ?? 'all';

            // Kullanıcı bilgilerini al
            $stmt = $pdo->prepare("SELECT id, username, base_currency FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = [
                'status' => 'success',
                'data' => [
                    'user' => $user
                ]
            ];

            // Yükleme tipine göre verileri getir
            switch ($load_type) {
                case 'income':
                    $incomes = loadIncomes();
                    $response['data']['incomes'] = $incomes;
                    break;

                case 'savings':
                    $savings = loadSavings();
                    $response['data']['savings'] = $savings;
                    break;

                case 'payments':
                    $payments = loadPayments();
                    $response['data']['payments'] = $payments;
                    break;

                case 'recurring_payments':
                    $recurring_payments = loadRecurringPayments();
                    $response['data']['recurring_payments'] = $recurring_payments;
                    break;

                case 'summary':
                    $summary = loadSummary();
                    $response['data']['summary'] = $summary;
                    break;
            }
            break;

        case 'mark_payment_paid':
            if (markPaymentPaid()) {
                $response = ['status' => 'success', 'message' => 'Ödeme ödendi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Ödeme ödenemedi'];
            }
            break;

        case 'mark_income_received':
            if (markIncomeReceived()) {
                $response = ['status' => 'success', 'message' => 'Gelir alındı'];
            } else {
                $response = ['status' => 'error', 'message' => 'Gelir alınamadı'];
            }
            break;

        case 'update_saving':
            if (updateSaving()) {
                $response = ['status' => 'success', 'message' => 'Birikim güncellendi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Birikim güncellenemedi'];
            }
            break;

        case 'update_full_saving':
            if (updateFullSaving()) {
                $response = ['status' => 'success', 'message' => 'Birikim güncellendi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Birikim güncellenemedi'];
            }
            break;

        case 'transfer_unpaid_payments':
            try {
                if (transferUnpaidPayments()) {
                    $response = ['status' => 'success', 'message' => 'Ödemeler aktarıldı'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Ödemeler aktarılırken hata'];
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $response = ['status' => 'error', 'message' => 'Ödemeler aktarılırken hata: ' . $e->getMessage()];
            }
            break;

        case 'get_user_data':
            $stmt = $pdo->prepare("SELECT id, username, base_currency, theme_preference FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response = [
                    'status' => 'success',
                    'data' => $user
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Kullanıcı bilgileri bulunamadı'
                ];
            }
            break;

        case 'update_user_settings':
            if (updateUserSettings()) {
                $response = ['status' => 'success', 'message' => 'Kullanıcı ayarları güncellendi'];
            } else {
                $response = ['status' => 'error', 'message' => 'Kullanıcı ayarları güncellenemedi'];
            }
            break;

        case 'get_child_payments':
            // Önce ana kaydı al
            $stmt = $pdo->prepare("SELECT id, name, amount, currency, first_date, status, exchange_rate 
                                 FROM payments 
                                 WHERE id = ? AND user_id = ?");
            if (!$stmt->execute([$_POST['parent_id'], $user_id])) {
                $response = [
                    'status' => 'error',
                    'message' => 'Ana kayıt alınamadı'
                ];
                break;
            }
            $parent_payment = $stmt->fetch(PDO::FETCH_ASSOC);

            // Sonra child kayıtları al
            $stmt = $pdo->prepare("SELECT id, name, amount, currency, first_date, status, exchange_rate 
                                 FROM payments 
                                 WHERE parent_id = ? AND user_id = ?
                                 ORDER BY first_date ASC");
            if ($stmt->execute([$_POST['parent_id'], $user_id])) {
                $child_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [
                    'status' => 'success',
                    'data' => [
                        'parent' => $parent_payment,
                        'children' => $child_payments
                    ]
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Child ödemeler alınamadı'
                ];
            }
            break;

        case 'get_payment_details':
            try {
                $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['id'], $user_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($payment) {
                    $response = [
                        'status' => 'success',
                        'data' => $payment
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Ödeme bulunamadı'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
            break;

        case 'update_income':
            try {
                if (updateIncome()) {
                    $response = ['status' => 'success', 'message' => 'Gelir başarıyla güncellendi'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Gelir güncellenemedi'];
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;
    }

    // Response'u güvenli hale getir
    if (isset($response['data'])) {
        $response['data'] = sanitizeOutput($response['data']);
    }
    if (isset($response['message'])) {
        $response['message'] = sanitizeOutput($response['message']);
    }

    // Sayısal değerleri formatla
    if (isset($response['data']['summary'])) {
        $response['data']['summary']['total_income'] = formatNumber($response['data']['summary']['total_income']);
        $response['data']['summary']['total_expense'] = formatNumber($response['data']['summary']['total_expense']);
    }

    if (isset($response['data']['incomes'])) {
        foreach ($response['data']['incomes'] as &$income) {
            $income['amount'] = formatNumber($income['amount']);
            if (isset($income['exchange_rate'])) {
                $income['exchange_rate'] = formatNumber($income['exchange_rate']);
            }
        }
    }

    if (isset($response['data']['payments'])) {
        foreach ($response['data']['payments'] as &$payment) {
            $payment['amount'] = formatNumber($payment['amount']);
            if (isset($payment['exchange_rate'])) {
                $payment['exchange_rate'] = formatNumber($payment['exchange_rate']);
            }
        }
    }

    if (isset($response['data']['savings'])) {
        foreach ($response['data']['savings'] as &$saving) {
            $saving['target_amount'] = formatNumber($saving['target_amount']);
            $saving['current_amount'] = formatNumber($saving['current_amount']);
        }
    }

    if (isset($response['data']['recurring_payments'])) {
        foreach ($response['data']['recurring_payments'] as &$payment) {
            $payment['amount'] = formatNumber($payment['amount']);
            $payment['yearly_total'] = formatNumber($payment['yearly_total']);
        }
    }
}

echo json_encode($response);
