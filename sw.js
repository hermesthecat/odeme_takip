const CACHE_NAME = 'butce-kontrol-v1';
const OFFLINE_URL = 'offline.html';

const ASSETS_TO_CACHE = [
    '/',
    '/index.html',
    '/add-payment.html',
    '/add-income.html',
    '/add-saving.html',
    '/style.css',
    '/script.js',
    '/manifest.json',
    '/offline.html',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css',
    'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js',
    'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/tr.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Service Worker Kurulumu
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(ASSETS_TO_CACHE);
            })
    );
});

// Service Worker Aktivasyonu
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch İsteklerini Yönetme
self.addEventListener('fetch', (event) => {
    // API isteklerini yönet
    if (event.request.url.includes('api.exchangerate.host')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request);
                })
        );
        return;
    }

    // Diğer istekleri yönet
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                if (response) {
                    return response;
                }
                return fetch(event.request)
                    .then((response) => {
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                        return response;
                    })
                    .catch(() => {
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
}); 