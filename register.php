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
            margin: 100px auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container register-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3">Bütçe Takip</h2>
            <p class="text-muted">Hesap Oluştur</p>
        </div>

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
                        echo '<div class="alert alert-success">Kayıt başarılı! <a href="login.php">Giriş yapın</a></div>';
                    } else {
                        echo '<div class="alert alert-danger">Kayıt sırasında bir hata oluştu.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Veritabanı hatası: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="alert alert-danger"><ul class="mb-0">';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul></div>';
            }
        }
        ?>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>