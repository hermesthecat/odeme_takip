<?php
require_once __DIR__ . '/header.php';
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="container register-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3"><?php echo t('site_name'); ?></h2>
            <p class="text-muted"><?php echo t('register_title'); ?></p>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form id="registerForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('username'); ?></label>
                        <input type="text" class="form-control" name="username" required minlength="3">
                        <div class="form-text"><?php echo t('min_length', ['min' => 3]); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo t('password'); ?></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <div class="form-text"><?php echo t('min_length', ['min' => 6]); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo t('password_confirm'); ?></label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo t('base_currency'); ?></label>
                        <select class="form-select" name="base_currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?php echo t('currency.base_info'); ?></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo t('register'); ?></button>
                        <a href="login.php" class="btn btn-link"><?php echo t('login.have_account'); ?></a>
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