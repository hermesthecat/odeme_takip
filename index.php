<?php

require_once __DIR__ . '/header.php';

?>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-piggy-bank me-2"></i>
                Bütçe Takip
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Özellikler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Yorumlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="register.php">Kayıt Ol</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Finansal Özgürlüğünüzü Yönetin</h1>
            <p class="lead mb-4">
                Gelirlerinizi, giderlerinizi ve birikimlerinizi kolayca takip edin.
                Finansal hedeflerinize ulaşmak hiç bu kadar kolay olmamıştı.
            </p>
            <a href="register.php" class="btn btn-light btn-lg">Hemen Başlayın</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Özellikler</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h3>Gelir Takibi</h3>
                        <p>Tüm gelirlerinizi kategorize edin ve düzenli gelirlerinizi otomatik olarak takip edin.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-credit-card feature-icon"></i>
                        <h3>Gider Yönetimi</h3>
                        <p>Harcamalarınızı kontrol altında tutun ve ödeme planlarınızı kolayca yönetin.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-piggy-bank feature-icon"></i>
                        <h3>Birikim Hedefleri</h3>
                        <p>Finansal hedeflerinizi belirleyin ve ilerlemenizi görsel olarak takip edin.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonial">
        <div class="container">
            <h2 class="text-center mb-5">Kullanıcı Yorumları</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text">"Bu uygulama sayesinde finansal durumumu çok daha iyi kontrol edebiliyorum. Artık her kuruşumun nereye gittiğini biliyorum."</p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0">Ahmet Y.</h5>
                                    <small class="text-muted">Yazılım Geliştirici</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text">"Birikim hedeflerimi takip etmek artık çok kolay. Görsel grafikler motivasyonumu artırıyor."</p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0">Ayşe K.</h5>
                                    <small class="text-muted">Öğretmen</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text">"Düzenli ödemelerimi hiç kaçırmıyorum artık. Hatırlatma sistemi gerçekten çok işime yarıyor."</p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0">Mehmet S.</h5>
                                    <small class="text-muted">Esnaf</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Finansal Geleceğinizi Şekillendirin</h2>
            <p class="lead mb-4">Hemen ücretsiz hesap oluşturun ve finansal kontrolü elinize alın.</p>
            <a href="register.php" class="btn btn-primary btn-lg">Ücretsiz Başlayın</a>
        </div>
    </section>

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
                        <li><a href="#features" class="text-white">Özellikler</a></li>
                        <li><a href="#testimonials" class="text-white">Yorumlar</a></li>
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