// Verileri yükle
function loadData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    // Yükleme göstergelerini göster ve ilgili tabloları gizle
    $('#incomeLoadingSpinner').show();
    $('#savingsLoadingSpinner').show();
    $('#paymentsLoadingSpinner').show();
    $('#recurringPaymentsLoadingSpinner').show();
    $('#summaryLoadingSpinner').show();
    $('#cardLoadingSpinner').show();

    $('#incomeList').closest('.table').hide();
    $('#savingList').closest('.table').hide();
    $('#paymentList').closest('.table').hide();
    $('#recurringPaymentsList').closest('.table').hide();
    $('#cardList').closest('.table').hide();

    // Ana veriyi yükle
    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year,
        load_type: 'summary'
    }).done(function (response) {
        if (response.status === 'success') {
            // Global data değişkenini set et
            window.data = response.data;

            // Özet bilgileri güncelle
            updateSummary(response.data);
            $('#summaryLoadingSpinner').hide();

            // Diğer verileri lazy load et
            loadIncomeData();
            loadSavingsData();
            loadPaymentsData();
            loadRecurringPaymentsData();
            loadCardData();
        } else {
            console.error('Veri yükleme hatası:', response.message);
            hideAllLoadingSpinners();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX hatası:', textStatus, errorThrown);
        Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: 'Veri yükleme hatası',
            showConfirmButton: false,
            timer: 1500
        });
        hideAllLoadingSpinners();
    });
}

// Gelir verilerini yükle
function loadIncomeData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year,
        load_type: 'income'
    }).done(function (response) {
        if (response.status === 'success') {
            updateIncomeList(response.data.incomes);
            $('#incomeLoadingSpinner').hide();
            $('#incomeList').closest('.table').show();
        }
    });
}

// Ödeme yöntemi verilerini yükle
function loadCardData() {
    // Loading spinner'ı göster
    $('#cardLoadingSpinner').show();

    ajaxRequest({
        action: 'get_data',
        load_type: 'card'
    }).done(function (response) {
        if (response.status === 'success' && response.data && response.data.cards) {
            updateCardList(response.data.cards);
        } else {
            console.error('Kart verisi bulunamadı:', response);
        }
    }).fail(function (error) {
        Swal.fire({
            icon: 'error',
            title: translations.error || 'Hata',
            text: translations.card.load_error || 'Ödeme yöntemleri yüklenirken bir hata oluştu'
        });
    }).always(function () {
        $('#cardLoadingSpinner').hide();
    });
}

// Birikim verilerini yükle
function loadSavingsData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year,
        load_type: 'savings'
    }).done(function (response) {
        if (response.status === 'success') {
            updateSavingsList(response.data.savings);
            $('#savingsLoadingSpinner').hide();
            $('#savingList').closest('.table').show();
        }
    }).fail(function (error) {
        console.error("Error loading savings data:", error);
    });
}

// Ödeme verilerini yükle
function loadPaymentsData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year,
        load_type: 'payments'
    }).done(function (response) {
        if (response.status === 'success') {
            updatePaymentsList(response.data.payments);
            $('#paymentsLoadingSpinner').hide();
            $('#paymentList').closest('.table').show();
        }
    });
}

// Tekrarlayan ödeme verilerini yükle
function loadRecurringPaymentsData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year,
        load_type: 'recurring_payments'
    }).done(function (response) {
        if (response.status === 'success') {
            updateRecurringPaymentsList(response.data.recurring_payments);
            $('#recurringPaymentsLoadingSpinner').hide();
            $('#recurringPaymentsList').closest('.table').show();
        }
    });
}

// Tüm yükleniyor göstergelerini gizle
function hideAllLoadingSpinners() {
    $('#incomeLoadingSpinner').hide();
    $('#savingsLoadingSpinner').hide();
    $('#paymentsLoadingSpinner').hide();
    $('#recurringPaymentsLoadingSpinner').hide();
    $('#summaryLoadingSpinner').hide();
    $('#cardLoadingSpinner').hide();
}

// Sayfa yüklendiğinde
$(document).ready(function () {
    // URL'den tarih bilgisini al
    const { month, year } = getDateFromUrl();

    // Select elementlerini güncelle
    $('#monthSelect').val(month);
    $('#yearSelect').val(year);

    // Kullanıcı temasını yükle
    ajaxRequest({
        action: 'get_user_data'
    }).done(function (response) {
        if (response.status === 'success') {
            setTheme(response.data.theme_preference);
        }
    });

    // Verileri yükle
    loadData();

    // Select elementleri değiştiğinde URL'i güncelle
    $('#monthSelect, #yearSelect').on('change', function () {
        const currentMonth = $('#monthSelect').val();
        const currentYear = $('#yearSelect').val();
        updateUrl(currentMonth, currentYear);
        loadData();
    });

    // Form submit işlemleri
    $('[data-action]').click(function () {
        const type = $(this).data('type');
        const modal = new bootstrap.Modal(document.getElementById(`${type}Modal`));
        modal.show();
    });

    // Form submit işlemleri
    $('.modal form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serializeObject();
        const type = form.data('type');
        let action = '';

        if (type === 'update_saving') {
            action = 'update_saving';
        } else if (type === 'update_full_saving') {
            action = 'update_full_saving';
        } else if (type === 'login') {
            action = 'login';
        } else if (type === 'register') {
            action = 'register';
        } else {
            action = 'add_' + type;
        }

        // next_date değerini kaldır çünkü API'de hesaplanacak
        if (formData.next_date) {
            delete formData.next_date;
        }

        ajaxRequest({
            action: action,
            ...formData
        }).done(function (response) {
            if (response.status === 'success') {
                const modalElement = form.closest('.modal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                form[0].reset();
                loadData();
            }
        });
    });

    // Kullanıcı ayarları formu submit
    $('form[data-type="user_settings"]').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serializeObject();

        ajaxRequest({
            action: 'update_user_settings',
            ...formData
        }).done(function (response) {
            if (response.status === 'success') {
                const modalElement = form.closest('.modal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                setTheme(formData.theme_preference);
                loadData();
            }
        });
    });
}); 