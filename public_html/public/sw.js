// شركة جنين للتجميل Service Worker — Lightweight
const STATIC_CACHE = 'jenincare-static-v3';
const DYNAMIC_CACHE = 'jenincare-dynamic-v3';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) => {
            return Promise.all(
                names.filter(n => n !== STATIC_CACHE && n !== DYNAMIC_CACHE)
                     .map(n => caches.delete(n))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    if (request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/api/')) return;

    if (/\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then(cached => cached || fetch(request).then(res => {
                if (res.ok) { const clone = res.clone(); caches.open(STATIC_CACHE).then(c => c.put(request, clone)); }
                return res;
            }))
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).then(res => {
                if (res.ok) { const clone = res.clone(); caches.open(DYNAMIC_CACHE).then(c => c.put(request, clone)); }
                return res;
            }).catch(() => caches.match(request).then(cached => cached || caches.match('/')))
        );
    }
});

self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title || 'شركة جنين للتجميل', {
            body: data.body || '',
            icon: '/favicon.ico',
            vibrate: [100, 50, 100],
            data: { url: data.url || '/' }
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data.url || '/'));
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});
