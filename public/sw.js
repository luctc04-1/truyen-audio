// Service Worker for TruyenAudio Web Push Notifications

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(clients.claim());
});

self.addEventListener('push', (event) => {
  let data = {};
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = { title: 'Truyện Audio', content: event.data.text() };
    }
  }

  const title = data.title || 'Truyện Audio Hay';
  const options = {
    body: data.content || data.body || 'Bạn có thông báo mới từ TruyenAudio!',
    icon: data.icon || '/favicon-32x32.png',
    badge: '/favicon-16x16.png',
    tag: data.id || 'truyen-audio-notif',
    renotify: true,
    data: {
      url: data.action_url || data.url || '/',
    },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Focus existing tab if open
      for (const client of clientList) {
        if ('focus' in client) {
          if (client.url.includes(self.location.origin)) {
            client.navigate(targetUrl);
            return client.focus();
          }
        }
      }
      // Otherwise open new window
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
