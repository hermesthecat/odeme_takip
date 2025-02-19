// Verileri yükle
function loadData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year
    }).done(function (response) {
        if (response.status === 'success') {
            // Global data değişkenini set et
            window.data = response.data;

            updateIncomeList(response.data.incomes);
            updateSavingsList(response.data.savings);
            updatePaymentsList(response.data.payments);
            updateSummary(response.data);
        } else {
            console.error('Veri yükleme hatası:', response.message);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX hatası:', textStatus, errorThrown);
    });
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
        const action = type === 'update_saving' ? 'update_full_saving' : 'add_' + type;

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