// Liste işlemleri için yardımcı fonksiyonlar

// Sonraki ödeme tarihini hesaplama
export function calculateNextPaymentDate(firstPaymentDate, frequency) {
    const firstDate = new Date(firstPaymentDate);
    const today = new Date();

    if (frequency === '0') return firstDate;

    let nextDate = new Date(firstDate);
    while (nextDate <= today) {
        nextDate.setMonth(nextDate.getMonth() + parseInt(frequency));
    }

    return nextDate;
} 