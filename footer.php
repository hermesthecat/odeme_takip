<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript için dil çevirileri -->
<script>
    const translations = {
        auth: {
            login: {
                title: '<?php echo t('login.title'); ?>',
                loading: '<?php echo t('login.loading'); ?>',
                success: '<?php echo t('login.success'); ?>',
                error: '<?php echo t('login.error'); ?>',
                invalid: '<?php echo t('login.invalid'); ?>'
            },
            register: {
                title: '<?php echo t('register.title'); ?>',
                loading: '<?php echo t('register.loading'); ?>',
                success: '<?php echo t('register.success'); ?>',
                error: '<?php echo t('register.error_message'); ?>',
                password_mismatch: '<?php echo t('register.password_mismatch'); ?>'
            },
            logout: {
                confirm: '<?php echo t('logout_confirm'); ?>',
                yes: '<?php echo t('yes'); ?>',
                no: '<?php echo t('no'); ?>',
                success: '<?php echo t('logout_success'); ?>'
            },
            buttons: {
                login: '<?php echo t('login.title'); ?>',
                register: '<?php echo t('register.title'); ?>',
                cancel: '<?php echo t('cancel'); ?>',
                confirm: '<?php echo t('confirm'); ?>'
            }
        }
    };
</script>

<!-- Uygulama JavaScript dosyaları -->
<script src="js/auth.js"></script>