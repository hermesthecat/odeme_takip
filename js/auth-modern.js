$(document).ready(function () {
    // Login formu submit edildiğinde
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const username = $('input[name="username"]').val();
        const password = $('input[name="password"]').val();
        const remember_me = $('input[name="remember_me"]').is(':checked');

        // Loading göster
        Swal.fire({
            title: t('app.loading'),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // AJAX isteği
        $.ajax({
            url: 'api/auth.php',
            type: 'POST',
            data: {
                action: 'login',
                username: username,
                password: password,
                remember_me: remember_me
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: t('login.success'),
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'app.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: t('error'),
                        text: response.message,
                        confirmButtonText: t('confirm')
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: t('error'),
                    text: t('auth.invalid_credentials'),
                    confirmButtonText: t('confirm')
                });
            }
        });
    });

    // Register formu submit edildiğinde
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        const username = $('input[name="username"]').val();
        const password = $('input[name="password"]').val();
        const password_confirm = $('input[name="password_confirm"]').val();
        const base_currency = $('select[name="base_currency"]').val();

        // Şifre kontrolü
        if (password !== password_confirm) {
            Swal.fire({
                icon: 'error',
                title: t('error'),
                text: t('auth.password_mismatch'),
                confirmButtonText: t('confirm')
            });
            return;
        }

        // Loading göster
        Swal.fire({
            title: t('app.loading'),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // AJAX isteği
        $.ajax({
            url: 'api/auth.php',
            type: 'POST',
            data: {
                action: 'register',
                username: username,
                password: password,
                password_confirm: password_confirm,
                base_currency: base_currency
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: t('success'),
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: t('error'),
                        text: response.message,
                        confirmButtonText: t('confirm')
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: t('error'),
                    text: t('auth.register_error'),
                    confirmButtonText: t('confirm')
                });
            }
        });
    });

    // Çıkış yapma işlemi
    $('.logout-btn').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: t('logout_confirm'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('yes'),
            cancelButtonText: t('no'),
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/auth.php',
                    type: 'POST',
                    data: {
                        action: 'logout'
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.href = 'index.php';
                        }
                    }
                });
            }
        });
    });
});