<?php

require_once __DIR__ . '/header.php';

?>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 mb-4"><?php echo t('hero.title'); ?></h1>
            <p class="lead mb-4">
                <?php echo t('hero.description'); ?>
            </p>
            <a href="register.php" class="btn btn-light btn-lg"><?php echo t('hero.cta'); ?></a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5"><?php echo t('features.title'); ?></h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h3><?php echo t('features.income_tracking.title'); ?></h3>
                        <p><?php echo t('features.income_tracking.description'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-credit-card feature-icon"></i>
                        <h3><?php echo t('features.expense_management.title'); ?></h3>
                        <p><?php echo t('features.expense_management.description'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-piggy-bank feature-icon"></i>
                        <h3><?php echo t('features.savings_goals.title'); ?></h3>
                        <p><?php echo t('features.savings_goals.description'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonial">
        <div class="container">
            <h2 class="text-center mb-5"><?php echo t('testimonials.title'); ?></h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text"><?php echo t('testimonials.1.text'); ?></p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0"><?php echo t('testimonials.1.name'); ?></h5>
                                    <small class="text-muted"><?php echo t('testimonials.1.title'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text"><?php echo t('testimonials.2.text'); ?></p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0"><?php echo t('testimonials.2.name'); ?></h5>
                                    <small class="text-muted"><?php echo t('testimonials.2.title'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="card-text"><?php echo t('testimonials.3.text'); ?></p>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle fs-2 me-3"></i>
                                <div>
                                    <h5 class="mb-0"><?php echo t('testimonials.3.name'); ?></h5>
                                    <small class="text-muted"><?php echo t('testimonials.3.title'); ?></small>
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
            <h2 class="mb-4"><?php echo t('cta.title'); ?></h2>
            <p class="lead mb-4"><?php echo t('cta.description'); ?></p>
            <a href="register.php" class="btn btn-primary btn-lg"><?php echo t('cta.button'); ?></a>
        </div>
    </section>

    <?php

    require_once __DIR__ . '/footer_body.php';

    require_once __DIR__ . '/footer.php';
    ?>
</body>

</html>