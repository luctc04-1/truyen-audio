<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#09090b">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ @filemtime(public_path('favicon-16x16.png')) ?: time() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ @filemtime(public_path('apple-touch-icon.png')) ?: time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}">

    <title>{{ $seoTitle ?? 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Truyện Audio Hay - Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya, ngôn tình, tiên hiệp, trinh thám mượt mà chất lượng cao.' }}">
    <meta name="keywords" content="{{ $seoKeywords ?? 'truyện audio hay, nghe truyện audio, truyên audio hay, truyện đọc đêm khuya, nghe truyện online, audio truyện hay' }}">

    <!-- OpenGraph / Facebook / Zalo -->
    <meta property="og:type" content="{{ $seoOgType ?? 'website' }}">
    <meta property="og:site_name" content="Truyện Audio Hay">
    <meta property="og:title" content="{{ $seoTitle ?? 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online' }}">
    <meta property="og:description" content="{{ $seoDescription ?? 'Truyện Audio Hay - Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya mượt mà chất lượng cao.' }}">
    <meta property="og:image" content="{{ $seoImage ?? asset('favicon.ico') }}">
    <meta property="og:url" content="{{ $seoCanonical ?? request()->fullUrl() }}">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle ?? 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online' }}">
    <meta name="twitter:description" content="{{ $seoDescription ?? 'Truyện Audio Hay - Website nghe truyện audio chọn lọc hay nhất.' }}">
    <meta name="twitter:image" content="{{ $seoImage ?? asset('favicon.ico') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoCanonical ?? request()->fullUrl() }}">

    <!-- Default WebSite Schema.org -->
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Truyện Audio Hay',
            'alternateName' => 'Truyen Audio Hay',
            'url' => url('/'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @if (!empty($seoSchema))
    <!-- Additional Structured Data Schema.org -->
    <script type="application/ld+json">
        {!! json_encode($seoSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    @vite(['resources/js/main.js', 'resources/css/app.css'])
</head>

<body>
    <div id="app"></div>
</body>

</html>


