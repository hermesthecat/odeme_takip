// Form validasyonu için
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Hisse ekleme formunu AJAX ile gönderme
document.getElementById('hisseForm').addEventListener('submit', function (event) {
    event.preventDefault();

    // Form validasyonu
    if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }

    // Form verilerini al
    const sembol = document.getElementById('sembolInput').value.trim().toUpperCase();
    const adet = document.getElementById('adetInput').value;
    const alisFiyati = document.getElementById('alisFiyatiInput').value;
    const hisseAdi = document.querySelector('input[name="hisse_adi"]').value;

    // Tüm alanların dolu olduğunu kontrol et
    if (!sembol || !adet || !alisFiyati) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen tüm alanları doldurun.',
            confirmButtonText: 'Tamam'
        });
        return;
    }

    // AJAX isteği gönder
    fetch('api/borsa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'addStock',
            'sembol': sembol,
            'adet': adet,
            'alis_fiyati': alisFiyati,
            'alis_tarihi': new Date().toISOString().split('T')[0], // Bugünün tarihi
            'hisse_adi': hisseAdi
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Başarılı mesajı göster
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Hisse başarıyla eklendi.',
                    confirmButtonText: 'Tamam'
                });

                // Formu temizle
                document.getElementById('hisseForm').reset();
                document.getElementById('hisseForm').classList.remove('was-validated');

                // Portföy listesini güncelle
                portfoyGuncelle();

                // Yeni hisse formunu kapat
                const bsCollapse = new bootstrap.Collapse(document.getElementById('yeniHisseForm'));
                bsCollapse.hide();
            } else {
                // Hata mesajı göster
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Hisse eklenirken bir hata oluştu.',
                    confirmButtonText: 'Tamam'
                });
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Bir hata oluştu. Lütfen tekrar deneyin.',
                confirmButtonText: 'Tamam'
            });
        });
});

// Tahmini maliyet hesaplama
function tahminiMaliyetHesapla() {
    const adet = parseFloat(document.getElementById('adetInput').value) || 0;
    const fiyat = parseFloat(document.getElementById('alisFiyatiInput').value) || 0;
    const maliyet = adet * fiyat;
    document.getElementById('tahminiMaliyet').textContent =
        new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(maliyet);
}

// Input değişikliklerini dinle
document.getElementById('adetInput').addEventListener('input', tahminiMaliyetHesapla);
document.getElementById('alisFiyatiInput').addEventListener('input', tahminiMaliyetHesapla);

// Otomatik tamamlama stil güncellemesi
document.getElementById('sembolOnerileri').style.cssText = `
        position: absolute;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        background: white;
        border-radius: 0.375rem;
        border: 1px solid rgba(0,0,0,.125);
        margin-top: 2px;
    `;

// Öneri öğelerinin stilini güncelle
const style = document.createElement('style');
style.textContent = `
        .autocomplete-items div {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .autocomplete-items div:hover {
            background-color: #f8f9fa;
        }
        .autocomplete-items div:last-child {
            border-bottom: none;
        }
        .fiyat-bilgisi {
            float: right;
            color: #6c757d;
        }
        .form-floating > .form-control::placeholder {
            color: transparent;
        }
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            opacity: .65;
            transform: scale(.85) translateY(-.5rem) translateX(.15rem);
        }
    `;
document.head.appendChild(style);

// Mevcut JavaScript kodları buraya gelecek

// Mali durum grafiği için yeni fonksiyonlar
let maliDurumChart = null;

function maliDurumGrafigiGuncelle(portfoyData) {
    const ctx = document.getElementById('maliDurumGrafik').getContext('2d');
    const isDarkMode = localStorage.getItem('theme') === 'dark';

    // Eğer grafik zaten varsa yok et
    if (maliDurumChart) {
        maliDurumChart.destroy();
    }

    let toplamDeger = 0;
    let toplamKarZarar = 0;
    let toplamSatisKar = 0;
    const hisseler = [];
    const degerler = [];
    const renkler = [];

    // Verileri hazırla
    portfoyData.forEach(hisse => {
        const guncelDeger = parseFloat(hisse.anlik_fiyat) * parseInt(hisse.toplam_adet);
        toplamDeger += guncelDeger;

        if (guncelDeger > 0) {
            hisseler.push(hisse.sembol);
            degerler.push(guncelDeger);
            // Rastgele renk üret
            renkler.push('#' + Math.floor(Math.random() * 16777215).toString(16));
        }
    });

    // Grafiği oluştur
    maliDurumChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: hisseler,
            datasets: [{
                data: degerler,
                backgroundColor: renkler,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: isDarkMode ? '#e9ecef' : '#212529'
                    }
                },
                title: {
                    display: true,
                    text: 'Portföy Dağılımı',
                    color: isDarkMode ? '#e9ecef' : '#212529'
                }
            }
        }
    });

    // Özet bilgileri güncelle
    document.getElementById('toplamPortfoyDeger').textContent =
        new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(toplamDeger);

    // Kar/zarar ve satış karı bilgilerini güncelle
    let totalKarZarar = 0;
    let totalSatisKar = 0;

    // Tüm satış detaylarını topla
    document.querySelectorAll('.table-light').forEach(satisDetay => {
        const karZararHucresi = satisDetay.querySelector('td.kar, td.zarar');
        if (karZararHucresi) {
            const karZararText = karZararHucresi.textContent.trim();
            const karZararDeger = parseFloat(karZararText.replace(/[^0-9.-]+/g, ""));
            if (!isNaN(karZararDeger)) {
                totalSatisKar += karZararDeger;
            }
        }
    });

    // Aktif hisselerin kar/zararını topla
    document.querySelectorAll('.ana-satir td:nth-child(5)').forEach(hucre => {
        const karZararText = hucre.textContent.trim();
        const karZararDeger = parseFloat(karZararText.replace(/[^0-9.-]+/g, ""));
        if (!isNaN(karZararDeger)) {
            totalKarZarar += karZararDeger;
        }
    });

    document.getElementById('toplamKarZarar').textContent =
        new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(totalKarZarar);
    document.getElementById('toplamSatisKar').textContent =
        new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(totalSatisKar);

    // Renk sınıflarını güncelle
    document.getElementById('toplamKarZarar').className = totalKarZarar >= 0 ? 'text-success' : 'text-danger';
    document.getElementById('toplamSatisKar').className = totalSatisKar >= 0 ? 'text-success' : 'text-danger';
}

// Portföy güncelleme fonksiyonunu güncelle
function portfoyGuncelle() {
    fetch('api/borsa.php?liste=1')
        .then(response => response.text())
        .then(data => {
            document.getElementById('portfoyListesi').innerHTML = data;

            // Portföy verilerini topla
            const portfoyData = [];
            document.querySelectorAll('.ana-satir').forEach(row => {
                portfoyData.push({
                    sembol: row.dataset.sembol,
                    toplam_adet: parseInt(row.querySelector('.adet').textContent),
                    anlik_fiyat: parseFloat(row.querySelector('.anlik_fiyat').textContent)
                });
            });

            // Mali durum grafiğini güncelle
            maliDurumGrafigiGuncelle(portfoyData);

            // Kar/Zarar hücrelerinin renklerini düzenle - Ana satırlardaki tüm kar/zarar hücreleri
            document.querySelectorAll('.ana-satir td:nth-child(5)').forEach(hucre => {
                const deger = parseFloat(hucre.textContent.replace(/[^0-9.-]+/g, ""));
                if (!isNaN(deger)) {
                    if (deger >= 0) {
                        hucre.classList.add('kar');
                        hucre.classList.remove('zarar');
                    } else {
                        hucre.classList.add('zarar');
                        hucre.classList.remove('kar');
                    }
                }
            });

            // Satış karı hücrelerinin renklerini düzenle - Ana satırlardaki tüm satış karı hücreleri
            document.querySelectorAll('.ana-satir td:nth-child(6)').forEach(hucre => {
                const deger = parseFloat(hucre.textContent.replace(/[^0-9.-]+/g, ""));
                if (!isNaN(deger)) {
                    if (deger >= 0) {
                        hucre.classList.add('kar');
                        hucre.classList.add('satis-kar');
                        hucre.classList.remove('zarar');
                    } else {
                        hucre.classList.add('zarar');
                        hucre.classList.add('satis-kar');
                        hucre.classList.remove('kar');
                    }
                }
            });

            // Detay satırlarındaki kar/zarar hücrelerini düzenle
            document.querySelectorAll('.detay-satir td.kar, .detay-satir td.zarar').forEach(hucre => {
                const deger = parseFloat(hucre.textContent.replace(/[^0-9.-]+/g, ""));
                if (!isNaN(deger)) {
                    if (deger >= 0) {
                        hucre.classList.add('kar');
                        hucre.classList.remove('zarar');
                    } else {
                        hucre.classList.add('zarar');
                        hucre.classList.remove('kar');
                    }
                }
            });

            // Tıklama olaylarını ekle
            document.querySelectorAll('.ana-satir').forEach(row => {
                row.addEventListener('click', function () {
                    const sembol = this.dataset.sembol;
                    const detayRow = document.querySelector(`.detay-satir[data-sembol="${sembol}"]`);
                    const icon = this.querySelector('.fa-chevron-right, .fa-chevron-down');

                    if (icon) {
                        if (detayRow.style.display === 'none') {
                            detayRow.style.display = 'table-row';
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        } else {
                            detayRow.style.display = 'none';
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    }
                });
            });
        })
        .catch(error => console.error('Hata:', error));
}

// Toplu satış formunu göster
function topluSatisFormunuGoster(sembol, guncelFiyat, event) {
    if (event) {
        event.stopPropagation();
    }

    const detayRow = document.querySelector(`.detay-satir[data-sembol="${sembol}"]`);
    if (detayRow) {
        detayRow.style.display = 'table-row';
        const anaSatir = document.querySelector(`.ana-satir[data-sembol="${sembol}"]`);
        if (anaSatir) {
            const icon = anaSatir.querySelector('.fa-chevron-right, .fa-chevron-down');
            if (icon) {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            }
        }
    }

    const form = document.getElementById(`satis-form-${sembol}`);
    if (form) {
        form.style.display = 'block';
        document.getElementById(`satis-fiyat-${sembol}`).value = guncelFiyat;

        // Toplam satış adedi inputunu dinle
        const toplamAdetInput = document.getElementById(`toplam-satis-adet-${sembol}`);
        if (toplamAdetInput) {
            toplamAdetInput.addEventListener('input', function () {
                dagitimYap(sembol, this.value);
            });
        }

        // Fiyat inputunu dinle
        const fiyatInput = document.getElementById(`satis-fiyat-${sembol}`);
        if (fiyatInput) {
            fiyatInput.addEventListener('input', () => karZararHesapla(sembol));
        }
    }
}

// FIFO mantığına göre adetleri dağıt
function dagitimYap(sembol, toplamAdet) {
    const form = document.getElementById(`satis-form-${sembol}`);
    if (!form) return;

    // Tüm satırları alış tarihine göre sırala (FIFO)
    const satirlar = Array.from(form.querySelectorAll('tr[data-alis-tarihi]'))
        .sort((a, b) => new Date(a.dataset.alisTarihi) - new Date(b.dataset.alisTarihi));

    let kalanAdet = parseInt(toplamAdet) || 0;

    // Tüm checkboxları seç ve disabled yap
    satirlar.forEach(satir => {
        const checkbox = satir.querySelector('.satis-secim');
        if (checkbox) {
            checkbox.checked = true;
            checkbox.disabled = true;
        }
    });

    // FIFO mantığına göre en eskiden başlayarak dağıt
    satirlar.forEach(satir => {
        const adetInput = satir.querySelector('.satis-adet');
        const maxAdet = parseInt(satir.dataset.maxAdet);

        if (kalanAdet > 0) {
            const dagitilacakAdet = Math.min(kalanAdet, maxAdet);
            adetInput.value = dagitilacakAdet;
            adetInput.readOnly = true; // Kullanıcı değiştiremez
            kalanAdet -= dagitilacakAdet;
        } else {
            adetInput.value = 0;
            adetInput.readOnly = true;
        }
    });

    karZararHesapla(sembol);
}

// Kar/zarar hesaplama fonksiyonunu güncelle
function karZararHesapla(sembol) {
    const form = document.getElementById(`satis-form-${sembol}`);
    const fiyatInput = document.getElementById(`satis-fiyat-${sembol}`);
    const satisFiyati = fiyatInput ? (parseFloat(fiyatInput.value) || 0) : 0;
    let toplamKar = 0;

    if (form) {
        form.querySelectorAll('tr[data-alis-fiyati]').forEach(row => {
            const alisFiyati = parseFloat(row.dataset.alisFiyati);
            const adetInput = row.querySelector('.satis-adet');
            const adet = adetInput ? (parseFloat(adetInput.value) || 0) : 0;
            toplamKar += (satisFiyati - alisFiyati) * adet;
        });

        const karZararSpan = document.getElementById(`kar-zarar-${sembol}`);
        if (karZararSpan) {
            karZararSpan.textContent = toplamKar.toFixed(2) + ' ₺';
            karZararSpan.className = toplamKar >= 0 ? 'text-success' : 'text-danger';
        }
    }
}

// Satış kaydını kaydet
function topluSatisKaydet(sembol) {
    const form = document.getElementById(`satis-form-${sembol}`);
    const satisFiyati = document.getElementById(`satis-fiyat-${sembol}`).value;
    const toplamAdet = parseInt(document.getElementById(`toplam-satis-adet-${sembol}`).value) || 0;

    if (toplamAdet <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen satılacak adet giriniz!',
            confirmButtonText: 'Tamam'
        });
        return;
    }

    if (!satisFiyati || satisFiyati <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen geçerli bir satış fiyatı girin!',
            confirmButtonText: 'Tamam'
        });
        return;
    }

    // İlk kayıt ID'sini al
    const anaSatir = document.querySelector(`.ana-satir[data-sembol="${sembol}"]`);
    if (!anaSatir) return;

    const kayitIdler = anaSatir.querySelector('.btn-danger').getAttribute('onclick').match(/\d+/)[0];

    // Satış işlemini gerçekleştir
    fetch(`api/borsa.php?sat=1&id=${kayitIdler}&adet=${toplamAdet}&fiyat=${satisFiyati}`)
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Satış işlemi başarıyla gerçekleştirildi.',
                    confirmButtonText: 'Tamam'
                });
                topluSatisFormunuGizle(sembol);
                portfoyGuncelle();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Satış işlemi başarısız oldu!',
                    confirmButtonText: 'Tamam'
                });
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Satış işlemi sırasında bir hata oluştu!',
                confirmButtonText: 'Tamam'
            });
        });
}

// Toplu satış formunu gizle
function topluSatisFormunuGizle(sembol, event) {
    if (event) {
        event.stopPropagation();
    }
    const form = document.getElementById(`satis-form-${sembol}`);
    if (form) {
        form.style.display = 'none';

        // Form içindeki inputları sıfırla
        form.querySelectorAll('.satis-secim').forEach(checkbox => {
            checkbox.checked = false;
            const adetInput = checkbox.closest('tr').querySelector('.satis-adet');
            adetInput.disabled = true;
            adetInput.value = 0;
        });

        karZararHesapla(sembol);
    }
}

// Hisse sil
function hisseSil(ids, event) {
    if (event) {
        event.stopPropagation();
    }

    // ids'yi string'e çevir
    ids = ids.toString();

    const idList = ids.split(',');
    const message = idList.length > 1 ?
        'Bu hissenin tüm kayıtlarını silmek istediğinizden emin misiniz?' :
        'Bu hisse kaydını silmek istediğinizden emin misiniz?';
    
    Swal.fire({
        title: 'Emin misiniz?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/borsa.php?sil=' + encodeURIComponent(ids))
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        Swal.fire({
                            title: 'Silindi!',
                            text: 'Hisse başarıyla silindi.',
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        });
                        portfoyGuncelle();
                    } else {
                        Swal.fire({
                            title: 'Hata!',
                            text: 'Hisse silinirken bir hata oluştu!',
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Bir hata oluştu. Lütfen tekrar deneyin.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                });
        }
    });
}

// Hisse arama ve otomatik tamamlama
let typingTimer;
let lastSearchTerm = '';
const doneTypingInterval = 750; // 750ms bekleme süresi
const minSearchLength = 3; // Minimum 3 karakter
const sembolInput = document.getElementById('sembolInput');
const sembolOnerileri = document.getElementById('sembolOnerileri');

// Input event listener'ı güncellendi
sembolInput.addEventListener('input', function () {
    clearTimeout(typingTimer);
    sembolOnerileri.innerHTML = '';

    const searchTerm = this.value.trim();

    // 3 karakterden az ise hiçbir şey yapma
    if (searchTerm.length < minSearchLength) {
        lastSearchTerm = '';
        return;
    }

    // Aynı terim için tekrar arama yapma
    if (searchTerm === lastSearchTerm) {
        return;
    }

    // Yeterli süre bekledikten sonra aramayı yap
    typingTimer = setTimeout(() => {
        if (searchTerm.length >= minSearchLength) {
            lastSearchTerm = searchTerm;
            hisseAra();
        }
    }, doneTypingInterval);
});

function hisseAra() {
    const aranan = sembolInput.value.trim();

    // Minimum karakter kontrolünü tekrar yap
    if (!aranan || aranan.length < minSearchLength) {
        sembolOnerileri.innerHTML = '';
        return;
    }

    // Arama yapılıyor göstergesi
    sembolOnerileri.innerHTML = '<div class="text-muted"><small>Aranıyor...</small></div>';

    fetch('api/borsa.php?ara=' + encodeURIComponent(aranan))
        .then(response => response.json())
        .then(data => {
            // Arama sırasında input temizlendiyse sonuçları gösterme
            if (sembolInput.value.trim().length < minSearchLength) {
                sembolOnerileri.innerHTML = '';
                return;
            }

            sembolOnerileri.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                const div = document.createElement('div');
                div.innerHTML = 'Sonuç bulunamadı';
                sembolOnerileri.appendChild(div);
                return;
            }

            data.forEach(hisse => {
                const div = document.createElement('div');
                if (hisse.code === 'ERROR' || hisse.code === 'INFO') {
                    div.innerHTML = `<span style="color: red;">${hisse.title}</span>`;
                } else {
                    div.innerHTML = `<strong>${hisse.code}</strong> - ${hisse.title} <span class="fiyat-bilgisi">${hisse.price} ₺</span>`;
                    div.addEventListener('click', function () {
                        sembolInput.value = hisse.code;
                        document.getElementsByName('alis_fiyati')[0].value = hisse.price;
                        document.getElementsByName('hisse_adi')[0].value = hisse.title;
                        sembolOnerileri.innerHTML = '';
                        lastSearchTerm = hisse.code;
                    });
                }
                sembolOnerileri.appendChild(div);
            });
        })
        .catch(error => {
            console.error('Hata:', error);
            sembolOnerileri.innerHTML = '<div style="color: red;">Arama sırasında bir hata oluştu!</div>';
        });
}

// Sayfa yüklendiğinde ve her 5 dakikada bir güncelle
window.onload = function () {
    // Tema ayarını uygula
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.body.setAttribute('data-theme', savedTheme);
    }
    
    portfoyGuncelle();
    setInterval(portfoyGuncelle, 300000);
};

// Tıklama ile önerileri kapat
document.addEventListener('click', function (e) {
    if (e.target !== sembolInput) {
        sembolOnerileri.innerHTML = '';
    }
});

// Kullanıcı temasını yükle
ajaxRequest({
    action: 'get_user_data'
}).done(function (response) {
    if (response.status === 'success') {
        setTheme(response.data.theme_preference);
    }
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