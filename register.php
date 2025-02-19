<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $base_currency = $_POST['base_currency'] ?? 'TRY';

    $errors = [];

    // Kullanıcı adı kontrolü
    if (strlen($username) < 3) {
        $errors[] = "Kullanıcı adı en az 3 karakter olmalıdır.";
    }

    // Şifre kontrolü
    if (strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }

    // Şifre eşleşme kontrolü
    if ($password !== $password_confirm) {
        $errors[] = "Şifreler eşleşmiyor.";
    }

    // Kullanıcı adı benzersizlik kontrolü
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Bu kullanıcı adı zaten kullanılıyor.";
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency) VALUES (?, ?, ?)");

            if ($stmt->execute([$username, $hashed_password, $base_currency])) {
                $success_message = 'Kayıt başarılı! <a href="login.php">Giriş yapın</a>';
            } else {
                $error_message = 'Kayıt sırasında bir hata oluştu.';
            }
        } catch (PDOException $e) {
            $error_message = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Bütçe Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-container {
            max-width: 400px;
            margin: 50px auto;
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
            margin-top: 100px;
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

    <div class="container register-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3">Bütçe Takip</h2>
            <p class="text-muted">Hesap Oluştur</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" name="username" required minlength="3">
                        <div class="form-text">En az 3 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <div class="form-text">En az 6 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre Tekrar</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ana Para Birimi</label>
                        <select class="form-select" name="base_currency" required>
                            <option value="TRY">TRY - Türk Lirası</option>
                            <option value="USD">USD - Amerikan Doları</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                        <div class="form-text">Tüm hesaplamalar bu para birimi üzerinden yapılacaktır. Merak etmeyin, daha sonra değiştirebilirsiniz.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                        <a href="login.php" class="btn btn-link">Zaten hesabınız var mı? Giriş yapın</a>
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