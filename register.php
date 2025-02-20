<?php
require_once __DIR__ . '/header.php';
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

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
                <form id="registerForm">
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

    <?php require_once __DIR__ . '/footer.php'; ?>
</body>

</html>