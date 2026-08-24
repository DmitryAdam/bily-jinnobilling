// ponytail: no offline cache — install/activate were already disabled upstream,
// so the old fetch handler only broke requests. Empty handler keeps PWA installable.
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));

self.addEventListener('fetch', () => {});
