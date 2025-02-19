// Birikimler listesi işlemleri
import { loadSavings, saveSavings } from '../storage.js';
import { formatDate } from '../utils.js';
import { showAddSavingModal } from '../modals/index.js';

// Birikimler listesini güncelleme
export async function updateSavingList(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    console.log('Birikimler listesi güncelleniyor...');
    const tbody = document.getElementById('savingList');
    if (!tbody) {
        console.error('Birikimler listesi tablosu bulunamadı!');
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler listesi tablosu bulunamadı!'
        });
        return;
    }

    try {
        const savings = await loadSavings();
        console.log('Yüklenen birikimler:', savings);
        tbody.innerHTML = '';

        if (!Array.isArray(savings) || savings.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="7" class="text-center">Henüz birikim kaydı bulunmamaktadır.</td>';
            tbody.appendChild(row);
            return;
        }

        const startDate = new Date(selectedYear, selectedMonth, 1);
        const endDate = new Date(selectedYear, selectedMonth + 1, 0);
        console.log('Tarih aralığı:', { başlangıç: startDate, bitiş: endDate });

        savings.forEach((saving, index) => {
            try {
                const savingDate = new Date(saving.start_date);
                if (savingDate >= startDate && savingDate <= endDate) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${saving.name || '-'}</td>
                        <td>${saving.target_amount ? parseFloat(saving.target_amount).toFixed(2) : '0.00'}</td>
                        <td>${saving.current_amount ? parseFloat(saving.current_amount).toFixed(2) : '0.00'}</td>
                        <td>${saving.currency || '-'}</td>
                        <td>${formatDate(saving.start_date)}</td>
                        <td>${formatDate(saving.target_date)}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar ${saving.status === 'completed' ? 'bg-success' : 'bg-primary'}" 
                                         role="progressbar" 
                                         style="width: ${Math.min(100, saving.progress)}%" 
                                         aria-valuenow="${Math.min(100, saving.progress)}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        ${Math.round(saving.progress)}%
                                    </div>
                                </div>
                            </div>

                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm me-1" data-action="update-saving" data-index="${index}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-action="delete-saving" data-index="${index}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                }
            } catch (error) {
                console.error(`Birikim ${index + 1} gösterilirken hata oluştu:`, error);
            }
        });

        // Event listener'ları ekle
        tbody.querySelectorAll('button[data-action]').forEach(button => {
            button.addEventListener('click', handleSavingAction);
        });
    } catch (error) {
        console.error('Birikimler listesi güncellenirken hata:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler listesi güncellenirken hata oluştu: ' + error.message
        });
    }
}

// Birikim güncelleme
export async function updateSaving(index) {
    try {
        const savings = await loadSavings();
        const saving = savings[index];
        if (!saving) {
            console.error(`${index} indeksli birikim bulunamadı`);
            return;
        }
        await showAddSavingModal(saving, index);
    } catch (error) {
        console.error('Birikim güncellenirken hata:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikim güncellenirken hata oluştu: ' + error.message
        });
    }
}

// Birikim silme
export async function deleteSaving(index) {
    try {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu birikimi silmek istediğinizden emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            const savings = await loadSavings();
            if (!savings[index]) {
                console.error(`${index} indeksli birikim bulunamadı`);
                return;
            }

            savings.splice(index, 1);
            const success = await saveSavings(savings);

            if (success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Birikim başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                });
                window.location.reload();
            }
        }
    } catch (error) {
        console.error('Birikim silinirken hata:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikim silinirken hata oluştu: ' + error.message
        });
    }
}

// Birikim işlemlerini yönet
async function handleSavingAction(e) {
    const action = e.target.closest('button').dataset.action;
    const index = parseInt(e.target.closest('button').dataset.index);

    switch (action) {
        case 'update-saving':
            await updateSaving(index);
            break;
        case 'delete-saving':
            await deleteSaving(index);
            break;
    }
} 