// Form verilerini objeye çeviren yardımcı fonksiyon
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// AJAX istekleri için yardımcı fonksiyon
function ajaxRequest(data) {
    return $.ajax({
        url: 'api.php',
        type: 'POST',
        data: data,
        dataType: 'json'
    });
}

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
            updateRecurringPaymentsList(response.data.recurring_payments);
            updateSummary(response.data);
        } else {
            console.error('Veri yükleme hatası:', response.message);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX hatası:', textStatus, errorThrown);
    });
}

// Tekrarlama sıklığı çevirisi
function getFrequencyText(frequency) {
    const frequencies = {
        'none': 'Tekrar Yok',
        'monthly': 'Aylık',
        'bimonthly': '2 Ayda Bir',
        'quarterly': '3 Ayda Bir',
        'fourmonthly': '4 Ayda Bir',
        'fivemonthly': '5 Ayda Bir',
        'sixmonthly': '6 Ayda Bir',
        'yearly': 'Yıllık'
    };
    return frequencies[frequency] || frequency;
}

// Gelir listesini güncelle
function updateIncomeList(incomes) {
    const tbody = $('#incomeList');
    tbody.empty();

    if (incomes.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">Henüz bir gelir eklenmemiş.</p>
                </td>
            </tr>
        `);
        return;
    }

    incomes.forEach(function (income) {
        const isChild = income.parent_id !== null;
        const rowClass = isChild ? 'table-light' : '';
        let amountText = `${parseFloat(income.amount).toFixed(2)} ${income.currency}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (income.currency !== data.user.base_currency && income.exchange_rate) {
            const convertedAmount = parseFloat(income.amount) * parseFloat(income.exchange_rate);
            amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
        }

        tbody.append(`
                    <tr class="${rowClass}">
                        <td style="width: 50px;">
                            <button
                                class="btn btn-sm ${income.status === 'received' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAsReceived(${income.id})"
                                title="${income.status === 'received' ? 'Alınmadı olarak işaretle' : 'Alındı olarak işaretle'}"
                            >
                                <i class="bi ${income.status === 'received' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${income.name}</td>
                        <td>${amountText}</td>
                        <td>${income.currency}</td>
                        <td>${income.first_date}</td>
                        <td>${getFrequencyText(income.frequency)}</td>
                        <td>${income.next_income_date || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteIncome(${income.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
        `);
    });
}

// Gelir durumunu güncelle
function markAsReceived(id) {
    ajaxRequest({
        action: 'mark_income_received',
        id: id
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
        }
    });
}

// Birikimler listesini güncelle
function updateSavingsList(savings) {
    const tbody = $('#savingList');
    tbody.empty();

    if (savings.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">Henüz bir birikim eklenmemiş.</p>
                </td>
            </tr>
        `);
        return;
    }

    savings.forEach(function (saving) {
        const progress = (saving.current_amount / saving.target_amount) * 100;
        const progressClass = progress < 25 ? 'bg-danger' :
            progress < 50 ? 'bg-warning' :
                progress < 75 ? 'bg-info' :
                    'bg-success';
        tbody.append(`
            <tr>
                <td>${saving.name}</td>
                <td>${saving.target_amount}</td>
                <td>${saving.current_amount}</td>
                <td>${saving.currency}</td>
                <td>${saving.start_date}</td>
                <td>${saving.target_date}</td>
                <td>
                    <div class="progress mb-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted text-center d-block">%${progress.toFixed(0)}</small>
                </td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateSavingModal(${JSON.stringify(saving).replace(/"/g, '&quot;')})" title="Düzenle">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSaving(${saving.id})" title="Sil">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

// Birikim güncelleme
function updateSaving(id, current_amount) {
    ajaxRequest({
        action: 'update_saving',
        id: id,
        current_amount: current_amount
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
        }
    });
}

// Birikim güncelleme modalını aç
function openUpdateSavingModal(saving) {
    $('#update_saving_id').val(saving.id);
    $('#update_saving_name').val(saving.name);
    $('#update_saving_target_amount').val(saving.target_amount);
    $('#update_saving_current_amount').val(saving.current_amount);
    $('#update_saving_currency').val(saving.currency);
    $('#update_saving_start_date').val(saving.start_date);
    $('#update_saving_target_date').val(saving.target_date);

    const modal = new bootstrap.Modal(document.getElementById('updateSavingModal'));
    modal.show();
}

// Ödemeler listesini güncelle
function updatePaymentsList(payments) {
    const tbody = $('#paymentList');
    tbody.empty();

    if (payments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">Henüz bir ödeme eklenmemiş.</p>
                </td>
            </tr>
        `);
        return;
    }
    payments.forEach(function (payment) {
        const isChild = payment.parent_id !== null;
        const rowClass = isChild ? 'table-light' : '';
        let amountText = `${parseFloat(payment.amount).toFixed(2)} ${payment.currency}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
            const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
            amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
        }

        tbody.append(`
                    <tr class="${rowClass}">
                        <td style="width: 50px;">
                            <button
                                class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAsPaid(${payment.id})"
                                title="${payment.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                            >
                                <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${payment.name}</td>
                        <td>${amountText}</td>
                        <td>${payment.currency}</td>
                        <td>${payment.first_date}</td>
                        <td>${getFrequencyText(payment.frequency)}</td>
                        <td>${payment.next_payment_date || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
        `);
    });

    // Aktarma butonu satırı
    tbody.append(`
        <tr class="table-secondary">
            <td colspan="8" class="text-end">
                <button class="btn btn-warning" onclick="transferUnpaidPayments()">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Sonraki Aya Aktar
                </button>
            </td>
        </tr>
    `);
}

// Tekrarlayan ödemeleri güncelle
function updateRecurringPaymentsList(recurring_payments) {
    const tbody = $('#recurringPaymentsList');
    tbody.empty();

    let totalYearlyPayment = 0;

    recurring_payments.forEach(function (payment) {
        if (payment.currency === 'TRY') {
            totalYearlyPayment += parseFloat(payment.yearly_total);
        }

        // Taksit bilgisini parçala (örn: "2/5" -> [2, 5])
        const [paid, total] = payment.payment_status.split('/').map(Number);
        const progress = (paid / total) * 100;
        const progressClass = progress < 25 ? 'bg-danger' :
            progress < 50 ? 'bg-warning' :
                progress < 75 ? 'bg-info' :
                    'bg-success';

        tbody.append(`
            <tr class="payment-parent" data-payment-id="${payment.id}" style="cursor: pointer;">
                <td>
                    <i class="bi bi-chevron-right me-2 toggle-icon"></i>
                    ${payment.name}
                </td>
                <td>${parseFloat(payment.amount).toFixed(2)}</td>
                <td>${payment.currency}</td>
                <td>
                    <div class="progress mb-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted text-center d-block">${payment.payment_status}</small>
                </td>
                <td>${parseFloat(payment.yearly_total).toFixed(2)}</td>
            </tr>
            <tr class="payment-children d-none" data-parent-id="${payment.id}">
                <td colspan="5" class="p-0">
                    <div class="child-payments-container bg-opacity-10 p-3">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Durum</th>
                                        <th>Ödeme Tarihi</th>
                                        <th>Tutar</th>
                                        <th>Kur</th>
                                    </tr>
                                </thead>
                                <tbody class="child-payments" data-parent-id="${payment.id}">
                                    <!-- Child ödemeler buraya eklenecek -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Child ödemeleri yükle
        loadChildPayments(payment.id);
    });

    $('#totalYearlyPayment').text(totalYearlyPayment.toFixed(2));

    // Ana kayıtlara tıklama olayı ekle
    $('.payment-parent').on('click', function () {
        const paymentId = $(this).data('payment-id');
        const childrenRow = $(`.payment-children[data-parent-id="${paymentId}"]`);
        const toggleIcon = $(this).find('.toggle-icon');

        childrenRow.toggleClass('d-none');
        toggleIcon.toggleClass('bi-chevron-right bi-chevron-down');

        if (!childrenRow.hasClass('d-none')) {
            loadChildPayments(paymentId);
        }
    });
}

// Child ödemeleri yükle
function loadChildPayments(parentId) {
    ajaxRequest({
        action: 'get_child_payments',
        parent_id: parentId
    }).done(function (response) {
        if (response.status === 'success') {
            const { parent, children } = response.data;
            const tbody = $(`.child-payments[data-parent-id="${parentId}"]`);
            tbody.empty();

            // Ana kaydı ekle
            let parentAmountText = `${parseFloat(parent.amount).toFixed(2)} ${parent.currency}`;
            if (parent.currency !== data.user.base_currency && parent.exchange_rate) {
                const convertedAmount = parseFloat(parent.amount) * parseFloat(parent.exchange_rate);
                parentAmountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
            }

            tbody.append(`
                <tr class="table-primary" style="cursor: default;">
                    <td>
                        <button
                            class="btn btn-sm ${parent.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                            onclick="markAsPaid(${parent.id}); return false;"
                            title="${parent.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                        >
                            <i class="bi ${parent.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                        </button>
                    </td>
                    <td>${parent.first_date}</td>
                    <td>${parentAmountText}</td>
                    <td>${parent.currency}</td>
                </tr>
            `);

            // Child kayıtları ekle
            children.forEach(function (payment) {
                let amountText = `${parseFloat(payment.amount).toFixed(2)} ${payment.currency}`;
                if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
                    const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
                    amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
                }

                tbody.append(`
                    <tr style="cursor: default;">
                        <td>
                            <button
                                class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAsPaid(${payment.id}); return false;"
                                title="${payment.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                            >
                                <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${payment.first_date}</td>
                        <td>${amountText}</td>
                        <td>${payment.currency}</td>
                    </tr>
                `);
            });
        }
    });
}

// Ödeme durumunu güncelle
function markAsPaid(id) {
    ajaxRequest({
        action: 'mark_payment_paid',
        id: id
    }).done(function (response) {
        if (response.status === 'success') {
            // Tıklanan butonu bul
            const button = $(`button[onclick*="markAsPaid(${id})"]`);
            const isPaid = button.hasClass('btn-outline-success');
            
            // Butonun durumunu güncelle
            if (isPaid) {
                button.removeClass('btn-outline-success').addClass('btn-success');
                button.find('i').removeClass('bi-check-circle').addClass('bi-check-circle-fill');
                button.attr('title', 'Ödenmedi olarak işaretle');
            } else {
                button.removeClass('btn-success').addClass('btn-outline-success');
                button.find('i').removeClass('bi-check-circle-fill').addClass('bi-check-circle');
                button.attr('title', 'Ödendi olarak işaretle');
            }

            // Önce child payments container'ı bul
            const childPaymentsContainer = button.closest('.child-payments');
            if (childPaymentsContainer.length > 0) {
                // Child payment ise, parent_id'yi al
                const parentId = childPaymentsContainer.data('parent-id');
                // Parent satırı bul
                const parentRow = $(`.payment-parent[data-payment-id="${parentId}"]`);
                
                // Tüm child ödemeleri kontrol et
                const allButtons = childPaymentsContainer.find('button');
                const totalPayments = allButtons.length;
                const paidPayments = allButtons.filter('.btn-success').length;
                
                // Progress bar'ı güncelle
                const progress = (paidPayments / totalPayments) * 100;
                const progressClass = progress < 25 ? 'bg-danger' :
                    progress < 50 ? 'bg-warning' :
                        progress < 75 ? 'bg-info' :
                            'bg-success';

                const progressBar = parentRow.find('.progress-bar');
                progressBar.removeClass('bg-danger bg-warning bg-info bg-success')
                    .addClass(progressClass)
                    .css('width', progress + '%')
                    .attr('aria-valuenow', progress);

                // Ödeme durumu metnini güncelle
                parentRow.find('.text-muted').text(`${paidPayments}/${totalPayments}`);
            }
        }
    });
}

// Özet bilgileri güncelle
function updateSummary(data) {
    let totalIncome = 0;
    let totalExpense = 0;

    data.incomes.forEach(income => {
        let amount = parseFloat(income.amount);
        if (income.currency !== 'TRY' && income.exchange_rate) {
            amount *= parseFloat(income.exchange_rate);
        }
        totalIncome += amount;
    });

    data.payments.forEach(payment => {
        let amount = parseFloat(payment.amount);
        if (payment.currency !== 'TRY' && payment.exchange_rate) {
            amount *= parseFloat(payment.exchange_rate);
        }
        totalExpense += amount;
    });

    const balance = totalIncome - totalExpense;

    $('#monthlyIncome').text(totalIncome.toFixed(2) + ' TL');
    $('#monthlyExpense').text(totalExpense.toFixed(2) + ' TL');
    $('#monthlyBalance').text(balance.toFixed(2) + ' TL');
    $('#currentPeriod').text($('#monthSelect option:selected').text() + ' ' + $('#yearSelect').val());
}

// Silme işlemleri
function deleteIncome(id) {
    if (confirm('Bu geliri silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_income',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

function deleteSaving(id) {
    if (confirm('Bu birikimi silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_saving',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

function deletePayment(id) {
    if (confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_payment',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

// Ödenmemiş ödemeleri sonraki aya aktar
function transferUnpaidPayments() {
    if (!confirm('Ödenmemiş ödemeleri sonraki aya aktarmak istediğinizden emin misiniz?')) {
        return;
    }

    const month = parseInt($('#monthSelect').val()) + 1; // API'de 1'den başlıyor
    const year = parseInt($('#yearSelect').val());

    ajaxRequest({
        action: 'transfer_unpaid_payments',
        current_month: month,
        current_year: year
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
            // Sonraki aya geç
            nextMonth();
        } else {
            alert('Hata: ' + response.message);
        }
    });
}

// URL'den ay ve yıl bilgisini al
function getDateFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const month = params.get('month');
    const year = params.get('year');

    if (month !== null && year !== null) {
        return { month: parseInt(month), year: parseInt(year) };
    }

    // URL'de tarih yoksa mevcut ay/yıl
    const now = new Date();
    return { month: now.getMonth(), year: now.getFullYear() };
}

// URL'i güncelle
function updateUrl(month, year) {
    const url = new URL(window.location.href);
    url.searchParams.set('month', month);
    url.searchParams.set('year', year);
    window.history.pushState({}, '', url);
}

// Ay/yıl değişikliği
function previousMonth() {
    let month = parseInt($('#monthSelect').val());
    let year = parseInt($('#yearSelect').val());

    if (month === 0) {
        month = 11;
        year--;
    } else {
        month--;
    }

    $('#monthSelect').val(month);
    $('#yearSelect').val(year);
    updateUrl(month, year);
    loadData();
}

function nextMonth() {
    let month = parseInt($('#monthSelect').val());
    let year = parseInt($('#yearSelect').val());

    if (month === 11) {
        month = 0;
        year++;
    } else {
        month++;
    }

    $('#monthSelect').val(month);
    $('#yearSelect').val(year);
    updateUrl(month, year);
    loadData();
}

// Tema değiştirme fonksiyonu
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}

// Kullanıcı ayarları modalını aç
function openUserSettings() {
    // Mevcut ayarları yükle
    ajaxRequest({
        action: 'get_user_data'
    }).done(function (response) {
        if (response.status === 'success') {
            $('#user_base_currency').val(response.data.base_currency);
            $('#user_theme_preference').val(response.data.theme_preference);
            const modalElement = document.getElementById('userSettingsModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Kullanıcı bilgileri alınamadı:', response.message);
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