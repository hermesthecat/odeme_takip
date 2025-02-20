<?php
require_once __DIR__ . '/header.php';
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

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
                <form id="loginForm">
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

    <?php require_once __DIR__ . '/footer.php'; ?>
</body>

</html>