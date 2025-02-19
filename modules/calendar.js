// Takvim işlemleri modülü
import { loadPayments, loadIncomes } from './storage.js';
import { formatDate, getFrequencyText } from './utils.js';

// Takvim olaylarını oluştur
async function createCalendarEvents(payments) {
    console.log('Takvim olayları oluşturuluyor...');
    console.log('Gelen ödemeler:', payments);

    const events = [];
    const today = new Date();
    const sixMonthsLater = new Date();
    sixMonthsLater.setMonth(today.getMonth() + 6);

    // Ödemeleri ekle
    if (Array.isArray(payments)) {
        console.log(`${payments.length} adet ödeme işlenecek`);
        payments.forEach((payment, index) => {
            if (!payment.first_payment_date) {
                console.error(`Ödeme #${index + 1} için first_payment_date eksik:`, payment);
                return;
            }

            let currentDate = new Date(payment.first_payment_date);
            let repeatCounter = 0;

            while (currentDate <= sixMonthsLater) {
                // Tekrar sayısı kontrolü
                if (payment.repeat_count !== null && repeatCounter >= payment.repeat_count) {
                    break;
                }

                events.push({
                    title: `${payment.name || 'İsimsiz Ödeme'} - ${payment.amount || '0'} ${payment.currency || 'TRY'}`,
                    start: currentDate.toISOString().split('T')[0],
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    extendedProps: {
                        type: 'payment',
                        amount: payment.amount || 0,
                        currency: payment.currency || 'TRY',
                        frequency: payment.frequency || '0',
                        repeatCount: payment.repeat_count,
                        currentRepeat: repeatCounter + 1
                    }
                });

                if (payment.frequency === '0') break;

                const nextDate = new Date(currentDate);
                nextDate.setMonth(nextDate.getMonth() + parseInt(payment.frequency || '0'));
                currentDate = nextDate;
                repeatCounter++;
            }
        });
    } else {
        console.error('Ödemeler bir dizi değil:', payments);
    }

    // Gelirleri ekle
    console.log('Gelirler yükleniyor...');
    const incomes = await loadIncomes();
    console.log('Gelen gelirler:', incomes);

    if (Array.isArray(incomes)) {
        console.log(`${incomes.length} adet gelir işlenecek`);
        incomes.forEach((income, index) => {
            console.log(`Gelir #${index + 1} işleniyor:`, income);
            
            if (!income.first_income_date) {
                console.error(`Gelir #${index + 1} için first_income_date eksik:`, income);
                return;
            }

            let currentDate = new Date(income.first_income_date);
            let repeatCounter = 0;

            console.log(`Gelir #${index + 1} için başlangıç tarihi:`, currentDate);

            while (currentDate <= sixMonthsLater) {
                // Tekrar sayısı kontrolü
                if (income.repeat_count !== null && repeatCounter >= income.repeat_count) {
                    console.log(`Gelir #${index + 1} için tekrar sayısı limitine ulaşıldı:`, repeatCounter);
                    break;
                }

                const event = {
                    title: `${income.name || 'İsimsiz Gelir'} - ${income.amount || '0'} ${income.currency || 'TRY'}`,
                    start: currentDate.toISOString().split('T')[0],
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    extendedProps: {
                        type: 'income',
                        amount: income.amount || 0,
                        currency: income.currency || 'TRY',
                        frequency: income.frequency || '0',
                        repeatCount: income.repeat_count,
                        currentRepeat: repeatCounter + 1
                    }
                };

                console.log(`Gelir #${index + 1} için etkinlik oluşturuldu:`, event);
                events.push(event);

                if (income.frequency === '0') {
                    console.log(`Gelir #${index + 1} tek seferlik, döngüden çıkılıyor`);
                    break;
                }

                const nextDate = new Date(currentDate);
                nextDate.setMonth(nextDate.getMonth() + parseInt(income.frequency || '0'));
                console.log(`Gelir #${index + 1} için sonraki tarih:`, nextDate);
                currentDate = nextDate;
                repeatCounter++;
            }
        });
    } else {
        console.error('Gelirler bir dizi değil:', incomes);
    }

    console.log(`Toplam ${events.length} adet etkinlik oluşturuldu:`, events);
    return events;
}

// Takvimi güncelle
export async function updateCalendar(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    console.log(`Takvim güncelleniyor... Yıl: ${selectedYear}, Ay: ${selectedMonth}`);
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Takvim elementi bulunamadı!');
        return;
    }

    try {
        const payments = await loadPayments();
        console.log('Yüklenen ödemeler:', payments);
        
        if (!Array.isArray(payments)) {
            console.error('Ödemeler bir dizi değil:', payments);
            return;
        }

        const events = await createCalendarEvents(payments);
        console.log('Oluşturulan etkinlikler:', events);

        if (!calendarEl.fullCalendar) {
            console.log('Yeni takvim oluşturuluyor...');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'tr',
                initialView: 'dayGridMonth',
                initialDate: new Date(selectedYear, selectedMonth, 1),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                events: events,
                eventClick: function (info) {
                    Swal.fire({
                        title: `<i class="bi bi-${info.event.extendedProps.type === 'payment' ? 'credit-card-fill text-danger' : 'wallet-fill text-success'} me-2"></i>${info.event.extendedProps.type === 'payment' ? 'Ödeme Detayları' : 'Gelir Detayları'}`,
                        html: `
                            <div class="income-details">
                                <div class="detail-item">
                                    <i class="bi bi-tag-fill text-primary"></i>
                                    <div class="detail-content">
                                        <span class="detail-label">İsim</span>
                                        <span class="detail-value">${info.event.title.split(' - ')[0]}</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-cash-stack text-success"></i>
                                    <div class="detail-content">
                                        <span class="detail-label">Tutar</span>
                                        <span class="detail-value">${info.event.extendedProps.amount} ${info.event.extendedProps.currency}</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-calendar-event text-info"></i>
                                    <div class="detail-content">
                                        <span class="detail-label">Tarih</span>
                                        <span class="detail-value">${formatDate(info.event.start)}</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-arrow-repeat text-warning"></i>
                                    <div class="detail-content">
                                        <span class="detail-label">Tekrarlama Sıklığı</span>
                                        <span class="detail-value">${getFrequencyText(info.event.extendedProps.frequency)}${info.event.extendedProps.frequency !== '0' ?
                                (info.event.extendedProps.repeatCount ?
                                    ` (${info.event.extendedProps.currentRepeat}/${info.event.extendedProps.repeatCount} tekrar)` :
                                    ' (Sonsuz)') :
                                ''
                            }</span>
                                    </div>
                                </div>
                            </div>
                        `,
                        customClass: {
                            container: 'income-details-modal',
                            popup: 'income-details-popup',
                            content: 'income-details-content'
                        },
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                }
            });
            calendar.render();
            calendarEl.fullCalendar = calendar;
        } else {
            calendarEl.fullCalendar.removeAllEvents();
            calendarEl.fullCalendar.addEventSource(events);
            calendarEl.fullCalendar.gotoDate(new Date(selectedYear, selectedMonth, 1));
        }
    } catch (error) {
        console.error('Takvim güncellenirken bir hata oluştu:', error);
    }
} 