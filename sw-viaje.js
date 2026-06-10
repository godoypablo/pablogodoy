const CACHE_NAME = 'viaje-v1';
const urlsToCache = [
  '/viaje.php',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css'
];

// Instalar service worker y cachear archivos
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache).catch(err => {
        console.log('Algunos recursos no pudieron cachearse:', err);
        return cache.addAll(['/viaje-nzl-aus.php']);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activar y limpiar caches antiguos
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Estrategia: intentar red primero, cache como fallback
self.addEventListener('fetch', event => {
  // Ignorar requests no GET
  if (event.request.method !== 'GET') {
    return;
  }

  // Para rutas de la app del viaje
  if (event.request.url.includes('viaje.php') ||
      event.request.url.includes('viaje-nzl-aus') ||
      event.request.url.includes('manifest-viaje.json') ||
      event.request.url.includes('bootstrap')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Cachear respuestas exitosas
          if (!response || response.status !== 200 || response.type === 'error') {
            return response;
          }
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
          return response;
        })
        .catch(() => {
          // Si falla la red, usar cache
          return caches.match(event.request).then(response => {
            return response || new Response('Contenido no disponible offline', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
        })
    );
  }
});
