<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/utils.php';
require_once __DIR__ . '/api/validate.php';
require_once __DIR__ . '/api/currency.php';
require_once __DIR__ . '/api/xss.php';

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
                    $response = ['status' => 'success', 'message' => t('income.add_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('income.add_error')];
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
                    $response = ['status' => 'success', 'message' => t('saving.add_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('saving.add_error')];
                }
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'add_payment':
            try {
                if (addPayment()) {
                    $response = ['status' => 'success', 'message' => t('payment.add_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('payment.add_error')];
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
                $response = ['status' => 'success', 'message' => t('income.delete_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('income.delete_error')];
            }
            break;

        case 'delete_saving':
            if (deleteSaving()) {
                $response = ['status' => 'success', 'message' => t('saving.delete_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('saving.delete_error')];
            }
            break;

        case 'delete_payment':
            if (deletePayment()) {
                $response = ['status' => 'success', 'message' => t('payment.delete_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('payment.delete_error')];
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
                $response = ['status' => 'success', 'message' => t('payment.mark_paid.success')];
            } else {
                $response = ['status' => 'error', 'message' => t('payment.mark_paid.error')];
            }
            break;

        case 'mark_income_received':
            if (markIncomeReceived()) {
                $response = ['status' => 'success', 'message' => t('income.mark_received.success')];
            } else {
                $response = ['status' => 'error', 'message' => t('income.mark_received.error')];
            }
            break;

        case 'update_saving':
            if (updateSaving()) {
                $response = ['status' => 'success', 'message' => t('saving.update_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('saving.update_error')];
            }
            break;

        case 'update_full_saving':
            if (updateFullSaving()) {
                $response = ['status' => 'success', 'message' => t('saving.update_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('saving.update_error')];
            }
            break;

        case 'transfer_unpaid_payments':
            try {
                if (transferUnpaidPayments()) {
                    $response = ['status' => 'success', 'message' => t('transfer.success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('transfer.error')];
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $response = ['status' => 'error', 'message' => t('transfer.error') . ': ' . $e->getMessage()];
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
                    'message' => t('user.not_found')
                ];
            }
            break;

        case 'update_user_settings':
            if (updateUserSettings()) {
                $response = ['status' => 'success', 'message' => t('settings.save_success')];
            } else {
                $response = ['status' => 'error', 'message' => t('settings.save_error')];
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
                    'message' => t('payment.load_error')
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

                foreach ($child_payments as &$child_payment) {
                    $child_payment['formatted_first_date'] = formatDate($child_payment['first_date']);
                }
                unset($child_payment); // Referansı temizle

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
                    'message' => t('payment.load_error')
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
                        'message' => t('payment.not_found')
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
                    $response = ['status' => 'success', 'message' => t('income.edit_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('income.edit_error')];
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'get_last_child_income_date':
            try {
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    throw new Exception(t('income.not_found'));
                }

                require_once __DIR__ . '/api/income.php';
                $last_date = getLastChildIncomeDate($id);
                $response = ['status' => 'success', 'last_date' => $last_date];
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'update_payment':
            try {
                if (updatePayment()) {
                    $response = ['status' => 'success', 'message' => t('payment.edit_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('payment.edit_error')];
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'get_last_child_payment_date':
            try {
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    throw new Exception(t('payment.not_found'));
                }

                require_once __DIR__ . '/api/payments.php';
                $last_date = getLastChildPaymentDate($id);
                $response = ['status' => 'success', 'last_date' => $last_date];
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'get_saving_details':
            try {
                $stmt = $pdo->prepare("SELECT * FROM savings WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['id'], $user_id]);
                $saving = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($saving) {
                    // Eğer bu bir parent kayıt ise ve child'ları varsa
                    if ($saving['parent_id'] === null) {
                        $stmt = $pdo->prepare("SELECT current_amount FROM savings 
                                             WHERE parent_id = ? AND user_id = ? 
                                             ORDER BY created_at DESC LIMIT 1");
                        $stmt->execute([$saving['id'], $user_id]);
                        $last_child = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Eğer child varsa onun current_amount'unu kullan
                        if ($last_child) {
                            $saving['current_amount'] = $last_child['current_amount'];
                        }
                    }

                    $response = [
                        'status' => 'success',
                        'data' => $saving
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => t('saving.not_found')
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
            break;

        case 'get_savings_history':
            try {
                require_once __DIR__ . '/api/savings.php';
                $savings_history = getSavingsHistory($_POST['id']);
                $response = ['status' => 'success', 'data' => $savings_history];
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
            break;

        case 'generate_code':
            require_once __DIR__ . '/api/telegram.php';
            generate_telegram_code();
            break;

        case 'unlink_telegram':
            require_once __DIR__ . '/api/telegram.php';
            unlink_telegram();
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
