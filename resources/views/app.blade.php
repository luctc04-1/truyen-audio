<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#09090b">
    <meta name="color-scheme" content="dark light">
    <title>Truyện Audio | Nghe truyện Việt Nam</title>
    <meta name="description" content="Truyện Audio - Nghe truyện độc quyền mọi lúc mọi nơi. Khám phá hàng ngàn tập truyện audio từ ngôn tình, giang hồ đến trinh thám.">
    <style>
        html, body, #app { margin: 0; min-height: 100%; }
        html[data-shell="client"], html[data-shell="client"] body, html[data-shell="client"] #app { background: #09090b; color-scheme: dark; }
        html[data-shell="admin"], html[data-shell="admin"] body, html[data-shell="admin"] #app { background: #f3f6f9; color: #212529; color-scheme: light; }
    </style>
    <script>
        (function () {
            var admin = location.pathname === '/admin' || location.pathname.indexOf('/admin/') === 0;
            var shell = admin ? 'admin' : 'client';
            var bg = admin ? '#f3f6f9' : '#09090b';
            var root = document.documentElement;
            root.setAttribute('data-shell', shell);
            root.style.backgroundColor = bg;
            document.querySelector('meta[name="theme-color"]')?.setAttribute('content', bg);
            document.querySelector('meta[name="color-scheme"]')?.setAttribute('content', admin ? 'light' : 'dark');
            document.addEventListener('DOMContentLoaded', function () {
                document.body.style.backgroundColor = bg;
                if (admin) document.body.classList.add('admin-route');
                var app = document.getElementById('app');
                if (app) app.style.backgroundColor = bg;
            });
        })();
    </script>
    @vite(['resources/js/main.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
