<?php

// save log to mysql
function saveLog($message, $type = 'info', $method = '', $user_id = '')
{
    global $pdo;
    $sql = "INSERT INTO logs (log_text, type, log_method, user_id) VALUES (:message, :type, :method, :user_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'message' => $message,
        'type' => $type,
        'method' => $method,
        'user_id' => $user_id
    ]);
}

function getLogs()
{
    global $pdo;
    $sql = "SELECT * FROM logs";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
