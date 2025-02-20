<?php

require_once __DIR__ . '/config.php';

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_name'); ?> - <?php echo $site_slogan; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="login.css">

    <meta name="description" content="<?php echo $site_description; ?>">
    <meta name="keywords" content="<?php echo $site_keywords; ?>">
    <meta name="author" content="<?php echo $site_author; ?>">
    <meta name="robots" content="index, follow">

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
            },
            utils: {
                validation: {
                    required: '<?php echo t('utils.validation.required'); ?>',
                    numeric: '<?php echo t('utils.validation.numeric'); ?>',
                    date: '<?php echo t('utils.validation.date'); ?>',
                    currency: '<?php echo t('utils.validation.currency'); ?>',
                    frequency: '<?php echo t('utils.validation.frequency'); ?>',
                    min_value: '<?php echo t('utils.validation.min_value'); ?>',
                    max_value: '<?php echo t('utils.validation.max_value'); ?>',
                    error_title: '<?php echo t('utils.validation.error_title'); ?>',
                    confirm_button: '<?php echo t('utils.validation.confirm_button'); ?>'
                },
                session: {
                    error_title: '<?php echo t('utils.session.error_title'); ?>',
                    invalid_token: '<?php echo t('utils.session.invalid_token'); ?>'
                },
                frequency: {
                    none: '<?php echo t('utils.frequency.none'); ?>',
                    monthly: '<?php echo t('utils.frequency.monthly'); ?>',
                    bimonthly: '<?php echo t('utils.frequency.bimonthly'); ?>',
                    quarterly: '<?php echo t('utils.frequency.quarterly'); ?>',
                    fourmonthly: '<?php echo t('utils.frequency.fourmonthly'); ?>',
                    fivemonthly: '<?php echo t('utils.frequency.fivemonthly'); ?>',
                    sixmonthly: '<?php echo t('utils.frequency.sixmonthly'); ?>',
                    yearly: '<?php echo t('utils.frequency.yearly'); ?>'
                },
                form: {
                    income_name: '<?php echo t('utils.form.income_name'); ?>',
                    payment_name: '<?php echo t('utils.form.payment_name'); ?>',
                    amount: '<?php echo t('utils.form.amount'); ?>',
                    currency: '<?php echo t('utils.form.currency'); ?>',
                    date: '<?php echo t('utils.form.date'); ?>',
                    frequency: '<?php echo t('utils.form.frequency'); ?>',
                    saving_name: '<?php echo t('utils.form.saving_name'); ?>',
                    target_amount: '<?php echo t('utils.form.target_amount'); ?>',
                    current_amount: '<?php echo t('utils.form.current_amount'); ?>',
                    start_date: '<?php echo t('utils.form.start_date'); ?>',
                    target_date: '<?php echo t('utils.form.target_date'); ?>'
                }
            },
            savings: {
                no_data: '<?php echo t('saving.not_found'); ?>',
                delete: {
                    title: '<?php echo t('saving.delete_confirm'); ?>',
                    confirm: '<?php echo t('yes_delete'); ?>',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>'
                },
                progress: {
                    title: '<?php echo t('saving.progress'); ?>',
                    completed: '<?php echo t('saving.completed'); ?>',
                    on_track: '<?php echo t('saving.on_track'); ?>',
                    behind: '<?php echo t('saving.behind'); ?>',
                    ahead: '<?php echo t('saving.ahead'); ?>'
                }
            },
            income: {
                no_data: '<?php echo t('income.not_found'); ?>',
                mark_received: {
                    mark_as_received: '<?php echo t('income.mark_received'); ?>',
                    mark_as_not_received: '<?php echo t('income.mark_not_received'); ?>'
                },
                delete: {
                    title: '<?php echo t('income.delete_confirm'); ?>',
                    confirm: '<?php echo t('yes_delete'); ?>',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>'
                },
                modal: {
                    error_title: '<?php echo t('error'); ?>',
                    error_not_found: '<?php echo t('income.not_found'); ?>',
                    success_title: '<?php echo t('success'); ?>',
                    success_message: '<?php echo t('income.edit_success'); ?>',
                    error_message: '<?php echo t('income.edit_error'); ?>',
                    current_rate: '<?php echo t('currency.current_rate'); ?>'
                }
            },
            payment: {
                no_data: '<?php echo t('payment.not_found'); ?>',
                mark_paid: {
                    mark_as_paid: '<?php echo t('payment.mark_paid'); ?>',
                    mark_as_not_paid: '<?php echo t('payment.mark_not_paid'); ?>'
                },
                delete: {
                    title: '<?php echo t('payment.delete_confirm'); ?>',
                    confirm: '<?php echo t('yes_delete'); ?>',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>',
                    transfer: '<?php echo t('payment.transfer'); ?>'
                },
                modal: {
                    error_title: '<?php echo t('error'); ?>',
                    error_not_found: '<?php echo t('payment.not_found'); ?>',
                    success_title: '<?php echo t('success'); ?>',
                    success_message: '<?php echo t('payment.edit_success'); ?>',
                    error_message: '<?php echo t('payment.edit_error'); ?>',
                    current_rate: '<?php echo t('currency.current_rate'); ?>'
                },
                recurring: {
                    total_payment: '<?php echo t('payment.recurring.total_payment'); ?>',
                    pending_payment: '<?php echo t('payment.recurring.pending_payment'); ?>'
                }
            },
            transfer: {
                title: '<?php echo t('transfer.title'); ?>',
                confirm: '<?php echo t('transfer.confirm'); ?>',
                transfer_button: '<?php echo t('transfer.transfer_button'); ?>',
                cancel_button: '<?php echo t('transfer.cancel_button'); ?>',
                error: '<?php echo t('transfer.error'); ?>',
                success: '<?php echo t('transfer.success'); ?>'
            }
        };
    </script>
</head>