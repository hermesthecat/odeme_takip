<?php
require_once __DIR__ . '/header.php';
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="container login-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3"><?php echo $site_name; ?></h2>
            <p class="text-muted">Giriş Yap</p>
        </div>

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
                        <a href="register.php" class="btn btn-link">Hesabınız yok mu? Ücretsiz bir hesap oluşturun</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php

    require_once __DIR__ . '/footer_body.php';

    require_once __DIR__ . '/footer.php';
    ?>
</body>

</html>