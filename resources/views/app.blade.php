<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">

    <!-- Favicon & Touch Icon -->
    <link rel="icon" type="image/png" href="{{ asset('android-chrome-512x512.png') }}?v={{ @filemtime(public_path('android-chrome-512x512.png')) ?: time() }}">
    <link rel="apple-touch-icon" href="{{ asset('android-chrome-512x512.png') }}">

    <title>{{ $seoTitle ?? 'Truyện Audio 247 | Nghe Truyện Audio Hay Online Miễn Phí' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Truyện Audio 247 (truyenaudio247.com) - Nghe truyện audio online miễn phí với kho truyện phong phú: tiên hiệp, kiếm hiệp, ngôn tình, đọc truyện đêm khuya, trinh thám mượt mà chất lượng cao.' }}">
    <meta name="keywords" content="{{ $seoKeywords ?? 'truyện audio 247, truyenaudio247, nghe truyện audio, đọc truyện đêm khuya, nghe truyện online, audio truyện hay, truyện audio online' }}">
    <meta name="robots" content="{{ $seoRobots ?? 'index, follow' }}">

    <!-- Icons & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    <!-- OpenGraph / Facebook / Zalo -->
    <meta property="og:type" content="{{ $seoOgType ?? 'website' }}">
    <meta property="og:site_name" content="Truyện Audio 247">
    <meta property="og:title" content="{{ $seoTitle ?? 'Truyện Audio 247 | Nghe Truyện Audio Hay Online Miễn Phí' }}">
    <meta property="og:description" content="{{ $seoDescription ?? 'Truyện Audio 247 - Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya mượt mà chất lượng cao.' }}">
    <meta property="og:image" content="{{ $seoImage ?? asset('android-chrome-512x512.png') }}">
    <meta property="og:url" content="{{ $seoCanonical ?? request()->fullUrl() }}">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle ?? 'Truyện Audio 247 | Nghe Truyện Audio Hay Online Miễn Phí' }}">
    <meta name="twitter:description" content="{{ $seoDescription ?? 'Truyện Audio 247 - Website nghe truyện audio chọn lọc hay nhất.' }}">
    <meta name="twitter:image" content="{{ $seoImage ?? asset('android-chrome-512x512.png') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoCanonical ?? request()->fullUrl() }}">

@php
    $siteUrl = rtrim(url('/'), '/');
    $websiteSchema = [
        chr(64) . 'context' => 'https://schema.org',
        chr(64) . 'type' => 'WebSite',
        'name' => 'Truyện Audio 247',
        'alternateName' => 'Truyen Audio 247',
        'url' => $siteUrl,
        'potentialAction' => [
            chr(64) . 'type' => 'SearchAction',
            'target' => $siteUrl . '/library?search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
    <!-- Default WebSite Schema.org -->
    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @if (!empty($seoSchemaJson))
    <!-- Structured Data Schema.org (Pre-serialized Safe JSON) -->
    <script type="application/ld+json" id="server-seo-jsonld">
        {!! $seoSchemaJson !!}
    </script>
    @elseif (!empty($seoSchema))
    <!-- Additional Structured Data Schema.org -->
    <script type="application/ld+json" id="server-seo-jsonld">
        {!! json_encode($seoSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    @vite(['resources/js/main.js', 'resources/css/app.css'])
</head>

<body>
    <div id="app">
        @if (!empty($seoPreRender))
            <article class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">
                <h1>{{ $seoPreRender['h1'] ?? ($seoTitle ?? 'Truyện Audio 247') }}</h1>
                <p>{{ $seoPreRender['description'] ?? ($seoDescription ?? '') }}</p>
                @if (!empty($seoPreRender['author']))
                    <p>Tác giả: {{ $seoPreRender['author'] }}</p>
                @endif
                @if (!empty($seoPreRender['narrator']))
                    <p>Giọng đọc: {{ $seoPreRender['narrator'] }}</p>
                @endif
                @if (!empty($seoPreRender['episodes']) && count($seoPreRender['episodes']) > 0)
                    <h2>Danh sách các tập audio</h2>
                    <ul>
                        @foreach ($seoPreRender['episodes'] as $ep)
                            <li>{{ $ep['title'] ?? 'Tập truyện' }}</li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @else
            <article class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">
                <h1>Truyện Audio 247 - Nghe Truyện Audio Hay Online Miễn Phí</h1>
                <p>Truyện Audio 247 (truyenaudio247.com) - Kho nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya, ngôn tình, tiên hiệp, kiếm hiệp, trinh thám mượt mà chất lượng cao.</p>
            </article>
        @endif
    </div>
</body>

</html>
