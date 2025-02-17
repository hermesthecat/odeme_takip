// Takvim işlemleri modülü
import { loadPayments, loadIncomes } from './storage.js';
import { formatDate, getFrequencyText } from './utils.js';

// Takvim olaylarını oluştur
function createCalendarEvents(payments) {
    const events = [];
    const today = new Date();
    const sixMonthsLater = new Date();
    sixMonthsLater.setMonth(today.getMonth() + 6);

    // Ödemeleri ekle
    payments.forEach(payment => {
        let currentDate = new Date(payment.firstPaymentDate);
        let repeatCounter = 0;

        while (currentDate <= sixMonthsLater) {
            // Tekrar sayısı kontrolü
            if (payment.repeatCount !== null && repeatCounter >= payment.repeatCount) {
                break;
            }

            events.push({
                title: `${payment.name} - ${payment.amount} ${payment.currency}`,
                start: currentDate.toISOString().split('T')[0],
                backgroundColor: '#dc3545', // Kırmızı
                borderColor: '#dc3545',
                extendedProps: {
                    type: 'payment',
                    amount: payment.amount,
                    currency: payment.currency,
                    frequency: payment.frequency,
                    repeatCount: payment.repeatCount,
                    currentRepeat: repeatCounter + 1
                }
            });

            if (payment.frequency === '0') break;

            const nextDate = new Date(currentDate);
            nextDate.setMonth(nextDate.getMonth() + parseInt(payment.frequency));
            currentDate = nextDate;
            repeatCounter++;
        }
    });

    // Gelirleri ekle
    const incomes = loadIncomes();
    incomes.forEach(income => {
        let currentDate = new Date(income.firstIncomeDate);
        let repeatCounter = 0;

        while (currentDate <= sixMonthsLater) {
            // Tekrar sayısı kontrolü
            if (income.repeatCount !== null && repeatCounter >= income.repeatCount) {
                break;
            }

            events.push({
                title: `${income.name} - ${income.amount} ${income.currency}`,
                start: currentDate.toISOString().split('T')[0],
                backgroundColor: '#198754', // Yeşil
                borderColor: '#198754',
                extendedProps: {
                    type: 'income',
                    amount: income.amount,
                    currency: income.currency,
                    frequency: income.frequency,
                    repeatCount: income.repeatCount,
                    currentRepeat: repeatCounter + 1
                }
            });

            if (income.frequency === '0') break;

            const nextDate = new Date(currentDate);
            nextDate.setMonth(nextDate.getMonth() + parseInt(income.frequency));
            currentDate = nextDate;
            repeatCounter++;
        }
    });

    return events;
}

// Takvimi güncelle
export function updateCalendar(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const payments = loadPayments();
    const events = createCalendarEvents(payments);

    if (!calendarEl.fullCalendar) {
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
} 