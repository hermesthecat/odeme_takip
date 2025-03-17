<?php

/**
 * Ödeme Yöntemi yönetimi API'si
 * Card management API
 * 
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/../config.php';
checkLogin();

/**
 * Yeni ödeme yöntemi ekler
 * Adds new card
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function addCard()
{
    global $pdo, $user_id;

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('income.name'));
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO card (user_id, name) 
                     VALUES (?, ?)");

    if (!$stmt->execute([
        $user_id,
        $name
    ])) {
        throw new Exception(t('card.add_error'));
        saveLog("Ödeme yöntemi ekleme hatası ($name): " . $e->getMessage(), 'error', 'addCard', $_SESSION['user_id']);
        // Card addition error
    }

    $pdo->commit();
    return true;
}

/**
 * Ödeme yöntemi kaydını siler
 * Deletes card record
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function deleteCard()
{
    global $pdo, $user_id;

    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM card WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_POST['id'], $user_id])) {
        return true;
    } else {
        throw new Exception(t('card.delete_error'));
        saveLog("Ödeme yöntemi silme hatası ($id): " . $e->getMessage(), 'error', 'deleteCard', $_SESSION['user_id']);
    }
}

/**
 * Ödeme yöntemlerini listeler
 * Lists cards
 * 
 * @return array - Ödeme yöntemi listesi / Card list
 */
function loadCards()
{
    global $pdo, $user_id;

    $sql_cards = "SELECT * FROM card WHERE user_id = ?";

    $stmt_cards = $pdo->prepare($sql_cards);
    $stmt_cards->execute([$user_id]);
    $cards = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

    saveLog("Ödeme yöntemleri alındı: " . $user_id, 'info', 'loadCards', $_SESSION['user_id']);

    return $cards;
}

/**
 * Ödeme yöntemini günceller
 * Updates card
 * 
 * @return bool - İşlem başarılı/başarısız / Operation success/failure
 */
function updateCard()
{
    global $pdo, $user_id;

    if (isset($_POST['id'])) {
        $id = validateRequired($_POST['id'] ?? null, t('income.id'));
    }

    if (isset($_POST['name'])) {
        $name = validateRequired($_POST['name'] ?? null, t('income.name'));
    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("UPDATE card SET 
            name = ?
            WHERE id = ? AND user_id = ?");

        if (!$stmt->execute([
            $name,
            $id,
            $user_id
        ])) {
            throw new Exception(t('card.update_error'));
            saveLog("Ödeme yöntemi güncelleme hatası ($id): " . $e->getMessage(), 'error', 'updateCard', $_SESSION['user_id']); // Ödeme yöntemi ID
        } else {
            saveLog("Ödeme yöntemi güncellendi: $id", 'info', 'updateCard', $_SESSION['user_id']);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
