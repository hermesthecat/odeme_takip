<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $remember_me = isset($_POST['remember_me']);

    $sql = "SELECT id, username, password FROM users WHERE username = :username";

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $id = $row["id"];
                    $hashed_password = $row["password"];
                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION["user_id"] = $id;
                        $_SESSION["username"] = $username;

                        // Beni hatırla
                        if ($remember_me) {
                            $token = bin2hex(random_bytes(32));
                            setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 gün

                            // Token'ı veritabanına kaydet
                            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                            $stmt->execute([$token, $id]);
                        }

                        header("location: app.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Bütçe Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            flex: 1;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #0d6efd;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 50px 0;
            margin-top: auto;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-piggy-bank me-2"></i>
                Bütçe Takip
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">Özellikler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#testimonials">Yorumlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="register.php">Kayıt Ol</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container login-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3">Bütçe Takip</h2>
            <p class="text-muted">Giriş Yap</p>
        </div>

        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger"><?php echo $login_err; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember_me" id="remember_me">
                        <label class="form-check-label" for="remember_me">Beni Hatırla</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                        <a href="register.php" class="btn btn-link">Hesabınız yok mu? Kayıt olun</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Bütçe Takip</h5>
                    <p>Kişisel finans yönetimini kolaylaştıran modern çözüm.</p>
                </div>
                <div class="col-md-3">
                    <h5>Bağlantılar</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php#features" class="text-white">Özellikler</a></li>
                        <li><a href="index.php#testimonials" class="text-white">Yorumlar</a></li>
                        <li><a href="login.php" class="text-white">Giriş Yap</a></li>
                        <li><a href="register.php" class="text-white">Kayıt Ol</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>İletişim</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope me-2"></i> info@butcetakip.com</li>
                        <li><i class="bi bi-telephone me-2"></i> (0212) 555 0123</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Bütçe Takip. Tüm hakları saklıdır.</p>
                <small>Geliştirici: A. Kerem Gök</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>