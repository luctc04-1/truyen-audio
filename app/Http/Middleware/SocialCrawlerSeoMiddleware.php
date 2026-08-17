<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Models\Series;

class SocialCrawlerSeoMiddleware
{
    /**
     * Common social crawlers list
     */
    protected array $crawlers = [
        'facebookexternalhit',
        'Facebot',
        'ZaloBot',
        'Twitterbot',
        'TelegramBot',
        'WhatsApp',
        'LinkedInBot',
        'Pinterest',
        'Slackbot',
        'Googlebot',
        'bingbot',
        'duckduckbot',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $path = trim($request->getPathInfo(), '/');

        // Match /story/{identifier} where identifier can be id or id-slug or slug
        if (Str::startsWith($path, 'story/')) {
            $param = substr($path, 6);
            $this->injectStorySeo($param, $request->fullUrl());
        } elseif ($path === '' || $path === 'home') {
            View::share([
                'seoTitle'       => 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online',
                'seoDescription' => 'Truyện Audio Hay - Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya, ngôn tình, tiên hiệp, trinh thám mượt mà chất lượng cao.',
                'seoOgType'      => 'website',
            ]);
        } elseif ($path === 'library') {
            View::share([
                'seoTitle'       => 'Kho truyện | Truyện Audio Hay Chọn Lọc',
                'seoDescription' => 'Danh sách đầy đủ các bộ truyện audio ngôn tình, tiên hiệp, kiếm hiệp, trinh thám mượt mà nhất trên Truyện Audio Hay.',
                'seoOgType'      => 'website',
            ]);
        }

        return $next($request);
    }

    /**
     * Inject story SEO attributes into Blade views
     */
    protected function injectStorySeo(string $param, string $currentUrl): void
    {
        if (empty($param)) {
            return;
        }

        try {
            $series = null;

            // Check if param is UUID
            if (Str::isUuid($param)) {
                $series = Series::find($param);
            }

            // Fallback: search by slug
            if (!$series) {
                $series = Series::where('slug', $param)->first();
            }

            if (!$series) {
                return;
            }

            $title = $series->title . ' - Truyện Audio Hay';
            $rawDesc = $series->synopsis ?? $series->description ?? 'Nghe truyện audio ' . $series->title . ' miễn phí chất lượng cao trên Truyện Audio Hay.';
            $description = Str::limit(trim(strip_tags($rawDesc)), 160);
            $image = $series->cover_url ?? $series->image ?? asset('favicon.ico');
            $canonical = url('/story/' . $series->id . ($series->slug ? '-' . $series->slug : ''));

            $schema = [
                '@context'        => 'https://schema.org',
                '@type'           => 'Audiobook',
                'name'            => $series->title,
                'description'     => $description,
                'image'           => $image,
                'url'             => $canonical,
                'inLanguage'      => 'vi',
                'author'          => [
                    '@type' => 'Person',
                    'name'  => $series->author ?? 'Đang cập nhật',
                ],
                'publisher'       => [
                    '@type' => 'Organization',
                    'name'  => 'Truyện Audio Hay',
                ],
            ];

            View::share([
                'seoTitle'       => $title,
                'seoDescription' => $description,
                'seoImage'       => $image,
                'seoCanonical'   => $canonical,
                'seoOgType'      => 'book',
                'seoSchema'      => $schema,
            ]);
        } catch (\Throwable $e) {
            return;
        }
    }
}
