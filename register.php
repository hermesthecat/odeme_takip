<?php
require_once __DIR__ . '/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: app.php');
    exit;
}
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="container register-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3"><?php echo $site_name; ?></h2>
            <p class="text-muted"><?php echo t('register.title'); ?></p>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form id="registerForm" autocomplete="off" novalidate>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('username')); ?></label>
                        <input type="text" class="form-control" name="username" required
                            minlength="3" pattern="[a-zA-Z0-9_]{3,}"
                            title="<?php echo htmlspecialchars(t('auth.username_requirements')); ?>"
                            autocomplete="username">
                        <div class="form-text"><?php echo htmlspecialchars(t('min_length', ['min' => 3])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('password')); ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" required
                                minlength="8"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                title="<?php echo htmlspecialchars(t('auth.password_requirements')); ?>"
                                autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text password-requirements">
                            <ul class="mb-0">
                                <li class="length-check">En az 8 karakter</li>
                                <li class="uppercase-check">En az 1 büyük harf</li>
                                <li class="lowercase-check">En az 1 küçük harf</li>
                                <li class="number-check">En az 1 rakam</li>
                                <li class="special-check">En az 1 özel karakter (@$!%*?&)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('password_confirm')); ?></label>
                        <input type="password" class="form-control" name="password_confirm" required
                            autocomplete="new-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(t('base_currency')); ?></label>
                        <select class="form-select" name="base_currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?php echo htmlspecialchars(t('currencies.base_info')); ?></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo t('register.title'); ?></button>
                        <a href="login.php" class="btn btn-link text-decoration-none"><?php echo t('login.have_account'); ?></a>
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
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const password = this.querySelector('input[name="password"]').value;
            const confirmPassword = this.querySelector('input[name="password_confirm"]').value;

            if (password !== confirmPassword) {
                alert('<?php echo htmlspecialchars(t('auth.password_mismatch')); ?>');
                return;
            }

        });

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

        // Şifre gereksinimleri kontrolü
        const passwordInput = document.querySelector('input[name="password"]');
        passwordInput.addEventListener('input', function() {
            const password = this.value;

            document.querySelector('.length-check').classList.toggle('text-success', password.length >= 8);
            document.querySelector('.uppercase-check').classList.toggle('text-success', /[A-Z]/.test(password));
            document.querySelector('.lowercase-check').classList.toggle('text-success', /[a-z]/.test(password));
            document.querySelector('.number-check').classList.toggle('text-success', /\d/.test(password));
            document.querySelector('.special-check').classList.toggle('text-success', /[@$!%*?&]/.test(password));
        });
    </script>

    <style>
        .password-requirements {
            font-size: 0.875em;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .password-requirements li::before {
            content: '❌';
            margin-right: 5px;
        }

        .password-requirements li.text-success::before {
            content: '✅';
        }
    </style>
</body>

</html>