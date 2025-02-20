    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-piggy-bank me-2"></i>
                <?php echo $site_name; ?>
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
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="login.php">Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="register.php">Kayıt Ol</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="app.php">Uygulamaya Git</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger ms-2 logout-btn" href="javascript:void(0)"> <?php echo htmlspecialchars($_SESSION['username']); ?> <i class="bi bi-box-arrow-right ms-1"></i></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>