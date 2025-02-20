<?php
require_once __DIR__ . '/config.php';

// Tüm modalları include et
require_once __DIR__ . '/modals/income_modal.php';
require_once __DIR__ . '/modals/savings_modal.php';
require_once __DIR__ . '/modals/payment_modal.php';
require_once __DIR__ . '/modals/user_settings_modal.php';
?>

<script>
    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Gelirler için)
    document.getElementById('incomeFrequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('incomeEndDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });

    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Ödemeler için)
    document.getElementById('paymentFrequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('endDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });

    // Modal açıldığında tarihleri otomatik doldur
    document.addEventListener('DOMContentLoaded', function() {
        // Bugünün tarihini YYYY-MM-DD formatında al
        function getTodayDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        }

        // Gelir modalı için
        const incomeModal = document.getElementById('incomeModal');
        incomeModal.addEventListener('show.bs.modal', function() {
            this.querySelector('input[name="first_date"]').value = getTodayDate();
        });

        // Ödeme modalı için
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function() {
            this.querySelector('input[name="first_date"]').value = getTodayDate();
        });
    });

    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Ödeme güncelleme için)
    document.getElementById('update_payment_frequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('updatePaymentEndDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });
</script>