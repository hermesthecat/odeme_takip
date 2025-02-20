    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white"><?php echo t('site_name'); ?></h5>
                    <p class="text-white"><?php echo t('site_description'); ?></p>
                </div>
                <div class="col-md-3">
                    <h5 class="text-white"><?php echo t('footer.links'); ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php#features" class="text-white"><?php echo t('features.title'); ?></a></li>
                        <li><a href="index.php#testimonials" class="text-white"><?php echo t('testimonials.title'); ?></a></li>
                        <li><a href="login.php" class="text-white"><?php echo t('login.title'); ?></a></li>
                        <li><a href="register.php" class="text-white"><?php echo t('register.title'); ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="text-white"><?php echo t('footer.contact'); ?></h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope me-2"></i> info@butcetakip.com</li>
                        <li><i class="bi bi-telephone me-2"></i> 90 111 111 11 11</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo t('site_name'); ?>. <?php echo t('footer.copyright'); ?></p>
                <small><?php echo $site_author; ?></small>
            </div>
        </div>
    </footer>