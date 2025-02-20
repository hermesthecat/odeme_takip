<?php
require_once __DIR__ . '/header.php';
?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="container register-container">
        <div class="logo">
            <i class="bi bi-piggy-bank"></i>
            <h2 class="mt-3"><?php echo $site_name; ?></h2>
            <p class="text-muted">Ücretsiz bir hesap oluşturun</p>
        </div>

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
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
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

    <?php

    require_once __DIR__ . '/footer_body.php';

    require_once __DIR__ . '/footer.php';
    ?>
</body>

</html>