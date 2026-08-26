const CACHE_NAME = 'dpanel-cache-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' || !request.url.startsWith(self.location.origin)) {
        return;
    }

    // Cache-first for built static assets.
    if (request.url.includes('/build/')) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                cache.match(request).then(
                    (cached) =>
                        cached ||
                        fetch(request).then((response) => {
                            cache.put(request, response.clone());
                            return response;
                        })
                )
            )
        );
        return;
    }

    // Network-first for everything else (pages, API).
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
