const CACHE_NAME = 'brs-isp-cache-v1';
const urlsToCache = [
  '/',
  '/manifest.json',
  '/img/logo.png',
  '/fonts/inter/fonts.css',
  '/fonts/jetbrains/fonts.css',
  '/fonts/fa/all.min.css',
  '/js/alpine.min.js',
  '/js/chart.min.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});
