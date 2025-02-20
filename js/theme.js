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
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
    });
} 