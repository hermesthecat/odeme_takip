<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    $sql = "SELECT id, username, password FROM users WHERE username = :username";
    
    if($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        
        if($stmt->execute()) {
            if($stmt->rowCount() == 1) {
                if($row = $stmt->fetch()) {
                    $id = $row["id"];
                    $hashed_password = $row["password"];
                    if(password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION["user_id"] = $id;
                        $_SESSION["username"] = $username;
                        header("location: index.php");
                        exit;
                    } else {
                        $login_err = "Geçersiz kullanıcı adı veya şifre.";
                    }
                }
            } else {
                $login_err = "Geçersiz kullanıcı adı veya şifre.";
            }
        } else {
            $login_err = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        }
        unset($stmt);
    }
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş - Bütçe Kontrol Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }
        .login-form {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>
<body>
    <main class="login-form">
        <div class="text-center mb-4">
            <h1>Bütçe Kontrol Sistemi</h1>
            <p>Lütfen giriş yapın</p>
        </div>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control" id="username" placeholder="Kullanıcı Adı" required>
                <label for="username">Kullanıcı Adı</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" id="password" placeholder="Şifre" required>
                <label for="password">Şifre</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Giriş Yap</button>
        </form>
    </main>
</body>
</html> 