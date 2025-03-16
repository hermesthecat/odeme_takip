<?php

require_once __DIR__ . '/header.php';

if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '" . SITE_URL . "/app.php';</script>";
    exit;
}
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="container login-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3"><?php echo $site_name; ?></h2>
            <p class="text-muted"><?php echo t('login.title'); ?></p>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form id="loginForm" autocomplete="off" novalidate>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('username')); ?></label>
                        <input type="text" class="form-control" name="username" required
                            autocomplete="username" pattern="[a-zA-Z0-9_]{3,}"
                            title="<?php echo htmlspecialchars(t('auth.username_requirements')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('password')); ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" required
                                autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember_me" id="remember_me">
                        <label class="form-check-label" for="remember_me"><?php echo t('remember_me'); ?></label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo t('login.title'); ?></button>
                        <a href="register.php" class="btn btn-link text-decoration-none"><?php echo t('login.no_account'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php

    require_once __DIR__ . '/footer_body.php';

    require_once __DIR__ . '/footer.php';
    ?>

    <script>
        // Şifre göster/gizle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>

</html>