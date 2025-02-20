<?php
return [
    // Genel
    'site_name' => 'Bütçe Takip',
    'site_description' => 'Kişisel finans yönetimini kolaylaştıran modern çözüm',
    'welcome' => 'Hoş Geldiniz',
    'logout' => 'Çıkış Yap',
    'settings' => 'Ayarlar',
    'save' => 'Kaydet',
    'cancel' => 'İptal',
    'delete' => 'Sil',
    'edit' => 'Düzenle',
    'update' => 'Güncelle',
    'yes' => 'Evet',
    'no' => 'Hayır',
    'confirm' => 'Onayla',
    'go_to_app' => 'Uygulamaya Git',

    // Giriş/Kayıt
    'username' => 'Kullanıcı Adı',
    'password' => 'Şifre',
    'remember_me' => 'Beni Hatırla',
    'login' => [
        'title' => 'Giriş',
        'error_message' => 'Geçersiz kullanıcı adı veya şifre.',
        'no_account' => 'Hesabınız yok mu? Ücretsiz bir hesap oluşturun',
        'success' => 'Giriş başarılı! Yönlendiriliyorsunuz...',
        'error' => 'Giriş yapılırken bir hata oluştu.',
        'required' => 'Lütfen kullanıcı adı ve şifrenizi girin.',
        'invalid' => 'Geçersiz kullanıcı adı veya şifre.',
        'locked' => 'Hesabınız kilitlendi. Lütfen daha sonra tekrar deneyin.',
        'inactive' => 'Hesabınız henüz aktif değil. Lütfen e-postanızı kontrol edin.',
        'have_account' => 'Hesabınız var mı? Giriş yapın'
    ],

    // Footer
    'footer' => [
        'links' => 'Bağlantılar',
        'contact' => 'İletişim',
        'copyright' => 'Tüm hakları saklıdır.'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => 'Finansal Özgürlüğünüzü Yönetin',
        'description' => 'Gelirlerinizi, giderlerinizi ve birikimlerinizi kolayca takip edin. Finansal hedeflerinize ulaşmak hiç bu kadar kolay olmamıştı.',
        'cta' => 'Hemen Başlayın'
    ],

    'features' => [
        'title' => 'Özellikler',
        'income_tracking' => [
            'title' => 'Gelir Takibi',
            'description' => 'Tüm gelirlerinizi kategorize edin ve düzenli gelirlerinizi otomatik olarak takip edin.'
        ],
        'expense_management' => [
            'title' => 'Gider Yönetimi',
            'description' => 'Harcamalarınızı kontrol altında tutun ve ödeme planlarınızı kolayca yönetin.'
        ],
        'savings_goals' => [
            'title' => 'Birikim Hedefleri',
            'description' => 'Finansal hedeflerinizi belirleyin ve ilerlemenizi görsel olarak takip edin.'
        ]
    ],

    'testimonials' => [
        'title' => 'Yorumlar',
        '1' => [
            'text' => '"Bu uygulama sayesinde finansal durumumu çok daha iyi kontrol edebiliyorum. Artık her kuruşumun nereye gittiğini biliyorum."',
            'name' => 'Ahmet Y.',
            'title' => 'Yazılım Geliştirici'
        ],
        '2' => [
            'text' => '"Birikim hedeflerimi takip etmek artık çok kolay. Görsel grafikler motivasyonumu artırıyor."',
            'name' => 'Ayşe K.',
            'title' => 'Öğretmen'
        ],
        '3' => [
            'text' => '"Düzenli ödemelerimi hiç kaçırmıyorum artık. Hatırlatma sistemi gerçekten çok işime yarıyor."',
            'name' => 'Mehmet S.',
            'title' => 'Esnaf'
        ]
    ],

    'cta' => [
        'title' => 'Finansal Geleceğinizi Şekillendirin',
        'description' => 'Hemen ücretsiz hesap oluşturun ve finansal kontrolü elinize alın.',
        'button' => 'Ücretsiz Başlayın'
    ],

    // Doğrulama
    'required' => 'Bu alan zorunludur',
    'min_length' => 'En az :min karakter olmalıdır',
    'max_length' => 'En fazla :max karakter olmalıdır',
    'email' => 'Geçerli bir e-posta adresi giriniz',
    'match' => 'Şifreler eşleşmiyor',
    'unique' => 'Bu değer zaten kullanılıyor',

    // Kimlik Doğrulama
    'password_confirm' => 'Şifre Tekrar',
    'forgot_password' => 'Şifremi Unuttum',
    'login_success' => 'Giriş başarılı!',
    'logout_confirm' => 'Çıkış yapmak istediğinize emin misiniz?',
    'logout_success' => 'Başarıyla çıkış yapıldı',

    // Gelirler
    'incomes' => 'Gelirler',
    'add_income' => 'Yeni Gelir Ekle',
    'edit_income' => 'Gelir Düzenle',
    'income_name' => 'Gelir İsmi',
    'income_amount' => 'Tutar',
    'income_date' => 'İlk Gelir Tarihi',
    'income_category' => 'Kategori',
    'income_note' => 'Not',
    'income_recurring' => 'Düzenli Gelir',
    'income_frequency' => 'Tekrarlama Sıklığı',
    'income_end_date' => 'Bitiş Tarihi',
    'income' => [
        'title' => 'Gelir',
        'add_success' => 'Gelir başarıyla eklendi',
        'add_error' => 'Gelir eklenirken bir hata oluştu',
        'edit_success' => 'Gelir başarıyla güncellendi',
        'edit_error' => 'Gelir güncellenirken bir hata oluştu',
        'delete_success' => 'Gelir başarıyla silindi',
        'delete_error' => 'Gelir silinirken bir hata oluştu',
        'delete_confirm' => 'Bu geliri silmek istediğinize emin misiniz?',
        'mark_received' => 'Alındı Olarak İşaretle',
        'mark_received_success' => 'Gelir alındı olarak işaretlendi',
        'mark_received_error' => 'Gelir alındı olarak işaretlenirken bir hata oluştu'
    ],

    // Ödemeler
    'payments' => 'Ödemeler',
    'add_payment' => 'Yeni Ödeme Ekle',
    'edit_payment' => 'Ödeme Düzenle',
    'payment_name' => 'Ödeme İsmi',
    'payment_amount' => 'Tutar',
    'payment_date' => 'Ödeme Tarihi',
    'payment_category' => 'Kategori',
    'payment_note' => 'Not',
    'payment_recurring' => 'Düzenli Ödeme',
    'payment_frequency' => 'Tekrarlama Sıklığı',
    'payment_end_date' => 'Bitiş Tarihi',
    'payment' => [
        'title' => 'Ödeme',
        'add_success' => 'Ödeme başarıyla eklendi',
        'add_error' => 'Ödeme eklenirken bir hata oluştu',
        'edit_success' => 'Ödeme başarıyla güncellendi',
        'edit_error' => 'Ödeme güncellenirken bir hata oluştu',
        'delete_success' => 'Ödeme başarıyla silindi',
        'delete_error' => 'Ödeme silinirken bir hata oluştu',
        'delete_confirm' => 'Bu ödemeyi silmek istediğinize emin misiniz?',
        'mark_paid' => 'Ödendi Olarak İşaretle',
        'mark_paid_success' => 'Ödeme ödendi olarak işaretlendi',
        'mark_paid_error' => 'Ödeme ödendi olarak işaretlenirken bir hata oluştu',
        'transfer_success' => 'Ödemeler başarıyla aktarıldı',
        'transfer_error' => 'Ödemeler aktarılırken bir hata oluştu',
        'transfer_confirm' => 'Ödenmemiş ödemeleri aktarmak istediğinize emin misiniz?'
    ],

    // Birikimler
    'savings' => 'Birikimler',
    'add_saving' => 'Yeni Birikim Ekle',
    'edit_saving' => 'Birikim Düzenle',
    'saving_name' => 'Birikim İsmi',
    'target_amount' => 'Hedef Tutar',
    'current_amount' => 'Biriken Tutar',
    'start_date' => 'Başlangıç Tarihi',
    'target_date' => 'Hedef Tarihi',
    'saving' => [
        'title' => 'Birikim',
        'add_success' => 'Birikim başarıyla eklendi',
        'add_error' => 'Birikim eklenirken bir hata oluştu',
        'edit_success' => 'Birikim başarıyla güncellendi',
        'edit_error' => 'Birikim güncellenirken bir hata oluştu',
        'delete_success' => 'Birikim başarıyla silindi',
        'delete_error' => 'Birikim silinirken bir hata oluştu',
        'delete_confirm' => 'Bu birikimi silmek istediğinize emin misiniz?',
        'progress' => 'İlerleme',
        'remaining' => 'Kalan Tutar',
        'remaining_days' => 'Kalan Gün',
        'monthly_needed' => 'Aylık Gereken Tutar',
        'completed' => 'Tamamlandı',
        'on_track' => 'Plana Uygun',
        'behind' => 'Plandan Geride',
        'ahead' => 'Plandan İleride'
    ],

    // Para Birimleri
    'currency' => 'Para Birimi',
    'base_currency' => 'Ana Para Birimi',
    'exchange_rate' => 'Kur',
    'update_rate' => 'Güncel Kur ile Güncelle',

    // Sıklık
    'frequency' => [
        'none' => 'Tek Seferlik',
        'daily' => 'Günlük',
        'weekly' => 'Haftalık',
        'monthly' => 'Aylık',
        'bimonthly' => '2 Ayda Bir',
        'quarterly' => '3 Ayda Bir',
        'fourmonthly' => '4 Ayda Bir',
        'fivemonthly' => '5 Ayda Bir',
        'sixmonthly' => '6 Ayda Bir',
        'yearly' => 'Yıllık'
    ],

    // Aylar
    'months' => [
        1 => 'Ocak',
        2 => 'Şubat',
        3 => 'Mart',
        4 => 'Nisan',
        5 => 'Mayıs',
        6 => 'Haziran',
        7 => 'Temmuz',
        8 => 'Ağustos',
        9 => 'Eylül',
        10 => 'Ekim',
        11 => 'Kasım',
        12 => 'Aralık'
    ],

    // Ayarlar
    'settings_title' => 'Kullanıcı Ayarları',
    'theme' => 'Tema',
    'theme_light' => 'Açık Tema',
    'theme_dark' => 'Koyu Tema',
    'language' => 'Dil',
    'current_password' => 'Mevcut Şifre',
    'new_password' => 'Yeni Şifre',
    'new_password_confirm' => 'Yeni Şifre Tekrar',

    // Hatalar
    'error' => 'Hata!',
    'success' => 'Başarılı!',
    'warning' => 'Uyarı!',
    'info' => 'Bilgi',
    'error_occurred' => 'Bir hata oluştu',
    'try_again' => 'Lütfen tekrar deneyin',
    'session_expired' => 'Oturumunuz sona erdi. Lütfen tekrar giriş yapın.',
    'not_found' => 'Sayfa bulunamadı',
    'unauthorized' => 'Yetkisiz erişim',
    'forbidden' => 'Erişim engellendi',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => 'Hesap Oluştur',
        'error_message' => 'Kayıt olurken bir hata oluştu.',
        'success' => 'Kayıt başarılı! Giriş yapabilirsiniz.',
        'username_taken' => 'Bu kullanıcı adı zaten kullanılıyor.',
        'password_mismatch' => 'Şifreler eşleşmiyor.',
        'invalid_currency' => 'Geçersiz para birimi seçimi.',
        'required' => 'Lütfen tüm alanları doldurun.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'Tüm hesaplamalar bu para birimi üzerinden yapılacaktır. Merak etmeyin, daha sonra değiştirebilirsiniz.',
        'try' => 'Türk Lirası',
        'usd' => 'Amerikan Doları',
        'eur' => 'Euro',
        'gbp' => 'İngiliz Sterlini'
    ],

    // Ayarlar
    'settings' => [
        'title' => 'Kullanıcı Ayarları',
        'base_currency' => 'Ana Para Birimi',
        'base_currency_info' => 'Tüm hesaplamalar bu para birimi üzerinden yapılacaktır.',
        'theme' => 'Tema',
        'theme_light' => 'Açık Tema',
        'theme_dark' => 'Koyu Tema',
        'theme_info' => 'Arayüz renk teması seçimi.',
        'language' => 'Dil',
        'language_info' => 'Arayüz dili seçimi.',
        'save_success' => 'Ayarlar başarıyla kaydedildi',
        'save_error' => 'Ayarlar kaydedilirken bir hata oluştu',
        'current_password' => 'Mevcut Şifre',
        'new_password' => 'Yeni Şifre',
        'new_password_confirm' => 'Yeni Şifre Tekrar',
        'password_success' => 'Şifre başarıyla değiştirildi',
        'password_error' => 'Şifre değiştirilirken bir hata oluştu',
        'password_mismatch' => 'Mevcut şifre yanlış',
        'password_same' => 'Yeni şifre eskisiyle aynı olamaz',
        'password_requirements' => 'Şifre en az 6 karakter olmalıdır'
    ],
];
