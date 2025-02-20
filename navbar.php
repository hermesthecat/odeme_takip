<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isset($_SESSION['user_id']);
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-piggy-bank me-2"></i>
            <?php echo t('site_name'); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (!$is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features"><?php echo t('features.title'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#testimonials"><?php echo t('testimonials.title'); ?></a>
                    </li>
                <?php endif; ?>

                <!-- Dil Seçimi -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-globe me-1"></i>
                        <?php echo $lang->getLanguageName($lang->getCurrentLanguage()); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach ($lang->getAvailableLanguages() as $code): ?>
                            <li>
                                <a class="dropdown-item <?php echo $lang->getCurrentLanguage() === $code ? 'active' : ''; ?>"
                                    href="?lang=<?php echo $code; ?>">
                                    <?php echo $lang->getLanguageName($code); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if (!$is_logged_in): ?>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="login.php"><?php echo t('login'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="register.php"><?php echo t('register'); ?></a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="app.php"><?php echo t('go_to_app'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger ms-2 logout-btn" href="javascript:void(0)">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <i class="bi bi-box-arrow-right ms-1"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>