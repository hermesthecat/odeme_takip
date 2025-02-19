<?php
require_once 'config.php';
checkLogin();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'add_income':
            $stmt = $pdo->prepare("INSERT INTO income (user_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $_POST['name'], $_POST['amount'], $_POST['currency'], $_POST['first_date'], $_POST['frequency'], $_POST['next_date']])) {
                $response = ['status' => 'success', 'message' => 'Gelir başarıyla eklendi'];
            }
            break;

        case 'add_saving':
            $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, target_amount, currency, start_date, target_date) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $_POST['name'], $_POST['target_amount'], $_POST['currency'], $_POST['start_date'], $_POST['target_date']])) {
                $response = ['status' => 'success', 'message' => 'Birikim başarıyla eklendi'];
            }
            break;

        case 'add_payment':
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, name, amount, currency, first_date, frequency, next_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $_POST['name'], $_POST['amount'], $_POST['currency'], $_POST['first_date'], $_POST['frequency'], $_POST['next_date']])) {
                $response = ['status' => 'success', 'message' => 'Ödeme başarıyla eklendi'];
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
            $month = $_POST['month'];
            $year = $_POST['year'];
            
            // Gelirleri al
            $stmt = $pdo->prepare("SELECT * FROM income WHERE user_id = ? AND MONTH(next_date) = ? AND YEAR(next_date) = ?");
            $stmt->execute([$user_id, $month, $year]);
            $incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Birikimleri al
            $stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ödemeleri al
            $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND MONTH(next_date) = ? AND YEAR(next_date) = ?");
            $stmt->execute([$user_id, $month, $year]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => [
                    'incomes' => $incomes,
                    'savings' => $savings,
                    'payments' => $payments
                ]
            ];
            break;
    }
}

echo json_encode($response); 