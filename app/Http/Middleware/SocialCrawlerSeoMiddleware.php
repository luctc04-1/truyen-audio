<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Models\Series;
use App\Models\SystemSetting;
use App\Modules\Series\Support\SeriesPresenter;

class SocialCrawlerSeoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 1. Load dynamic settings (cached for 1 hour)
            $settings = Cache::remember('system_seo_settings', 3600, function () {
                try {
                    return SystemSetting::pluck('setting_value', 'setting_key')->toArray();
                } catch (\Throwable $e) {
                    return [];
                }
            });

            $brandName = $settings['site_name'] ?? 'Truyện Audio 247';
            $defaultTitle = $settings['meta_title'] ?? 'Truyện Audio 247 | Nghe Truyện Audio Hay Online Miễn Phí';
            $defaultDesc = $settings['meta_description'] ?? 'Truyện Audio 247 (truyenaudio247.com) - Nghe truyện audio online miễn phí với kho truyện phong phú: tiên hiệp, kiếm hiệp, ngôn tình, đọc truyện đêm khuya, trinh thám mượt mà chất lượng cao.';
            $defaultImage = !empty($settings['og_image']) ? asset(ltrim($settings['og_image'], '/')) : asset('android-chrome-512x512.png');
            $defaultRobots = $settings['robots'] ?? 'index, follow';

            View::share([
                'seoTitle'       => $defaultTitle,
                'seoDescription' => $defaultDesc,
                'seoImage'       => $defaultImage,
                'seoRobots'      => $defaultRobots,
                'seoOgType'      => 'website',
                'seoCanonical'   => rtrim($request->url(), '/'),
            ]);

            $path = trim($request->getPathInfo(), '/');

            // Match /story/{identifier} where identifier can be id or slug
            if (Str::startsWith($path, 'story/')) {
                $param = substr($path, 6);
                $redirectResponse = $this->injectStorySeo($param, $request, $brandName, $defaultImage);
                if ($redirectResponse) {
                    return $redirectResponse;
                }
            } elseif ($path === '' || $path === 'home') {
                View::share([
                    'seoTitle'       => $defaultTitle,
                    'seoDescription' => $defaultDesc,
                    'seoOgType'      => 'website',
                    'seoCanonical'   => rtrim(url('/'), '/'),
                ]);
            } elseif ($path === 'library') {
                $category = $request->query('category');
                $genreLabel = '';
                if ($category) {
                    $labels = SeriesPresenter::genreLabelsForSlug($category);
                    $genreLabel = !empty($labels) ? $labels[0] : '';
                }

                $libTitle = $genreLabel
                    ? "Truyện Audio {$genreLabel} Hay Chọn Lọc | {$brandName}"
                    : "Kho Truyện Audio Chọn Lọc | {$brandName}";
                $libDesc = $genreLabel
                    ? "Danh sách đầy đủ các bộ truyện audio thể loại {$genreLabel} hay nhất chọn lọc, đọc truyện đêm khuya mượt mà chất lượng cao trên {$brandName} (truyenaudio247.com)."
                    : "Kho truyện audio chọn lọc hay nhất - Đầy đủ các thể loại ngôn tình, tiên hiệp, kiếm hiệp, trinh thám mượt mà chất lượng cao trên {$brandName}.";

                $canonical = url('/library') . ($category ? "?category={$category}" : '');

                View::share([
                    'seoTitle'       => $libTitle,
                    'seoDescription' => $libDesc,
                    'seoCanonical'   => $canonical,
                    'seoOgType'      => 'website',
                ]);
            } elseif ($path === 'community') {
                View::share([
                    'seoTitle'       => "Cộng đồng thính giả - {$brandName}",
                    'seoDescription' => "Tham gia thảo luận, đề xuất truyện hay và chia sẻ cảm nhận nghe truyện audio cùng cộng đồng thính giả trên {$brandName}.",
                    'seoCanonical'   => url('/community'),
                    'seoOgType'      => 'website',
                ]);
            } elseif ($path === 'vip') {
                View::share([
                    'seoTitle'       => "Nâng cấp VIP Không Giới Hạn - {$brandName}",
                    'seoDescription' => "Đăng ký hội viên VIP để nghe không giới hạn tất cả các bộ truyện audio chất lượng cao, không quảng cáo, truy cập sớm tập mới trên {$brandName}.",
                    'seoCanonical'   => url('/vip'),
                    'seoOgType'      => 'website',
                ]);
            }
        } catch (\Throwable $e) {
            // Silence any SEO middleware errors to guarantee the app always loads
        }

        return $next($request);
    }

    /**
     * Inject story SEO attributes and handle 301 redirect if accessing by UUID when slug exists
     */
    protected function injectStorySeo(string $param, Request $request, string $brandName, string $defaultImage)
    {
        if (empty($param)) {
            return null;
        }

        try {
            $series = null;
            $isUuid = Str::isUuid($param);

            if ($isUuid) {
                $series = Series::find($param);
                // 301 Permanent Redirect to canonical slug URL if series has slug and is standard GET request
                if ($series && !empty($series->slug) && $request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
                    return redirect('/story/' . $series->slug, 301);
                }
            } else {
                $series = Series::where('slug', $param)->first();
            }

            if (!$series) {
                return null;
            }

            $title = "Nghe Truyện Audio {$series->title} Full Mới Nhất - {$brandName}";
            $authorText = !empty($series->author) ? " tác giả {$series->author}" : '';
            $mcText = !empty($series->narrator) ? ", giọng đọc {$series->narrator}" : '';
            $rawDesc = $series->description ?? 'kho nghe truyện audio online miễn phí chất lượng cao.';
            $cleanDesc = trim(preg_replace('/\s+/', ' ', strip_tags($rawDesc)));
            $description = Str::limit("Nghe truyện audio {$series->title}{$authorText}{$mcText}. {$cleanDesc}", 160);

            $image = $series->cover_url ?? $defaultImage;
            $canonical = url('/story/' . ($series->slug ?? $series->id));

            // Load episodes for pre-render (for Googlebot / Cốc Cốc initial HTML)
            $episodes = $series->episodes()
                ->select(['id', 'title', 'episode_number', 'duration_seconds'])
                ->orderBy('episode_number', 'asc')
                ->limit(30)
                ->get()
                ->map(fn ($ep) => [
                    'title' => $ep->title ?: ('Tập ' . $ep->episode_number),
                ])
                ->toArray();

            // Build safe Schema without Blade directive conflicts
            $schema = [
                chr(64) . 'context'        => 'https://schema.org',
                chr(64) . 'type'           => 'Audiobook',
                'name'            => $series->title,
                'description'     => $description,
                'image'           => $image,
                'url'             => $canonical,
                'inLanguage'      => 'vi',
                'author'          => [
                    chr(64) . 'type' => 'Person',
                    'name'  => $series->author ?? 'Đang cập nhật',
                ],
                'publisher'       => [
                    chr(64) . 'type' => 'Organization',
                    'name'  => $brandName,
                ],
            ];

            if (!empty($series->narrator)) {
                $schema['readBy'] = [
                    chr(64) . 'type' => 'Person',
                    'name'  => $series->narrator,
                ];
            }

            if (!empty($series->average_rating) && (float) $series->average_rating > 0) {
                $schema['aggregateRating'] = [
                    chr(64) . 'type' => 'AggregateRating',
                    'ratingValue' => (float) $series->average_rating,
                    'ratingCount' => (int) ($series->ratings_count ?? 1),
                    'bestRating'  => 5,
                    'worstRating' => 1,
                ];
            }

            $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            View::share([
                'seoTitle'       => $title,
                'seoDescription' => $description,
                'seoKeywords'    => "{$series->title}, nghe truyện {$series->title}, truyện audio {$series->title}, audio {$series->title} full, nghe truyện audio, truyenaudio247, {$brandName}",
                'seoImage'       => $image,
                'seoCanonical'   => $canonical,
                'seoOgType'      => 'book',
                'seoSchemaJson'  => $schemaJson,
                'seoPreRender'   => [
                    'h1'          => "Nghe Truyện Audio {$series->title} Full",
                    'description' => $cleanDesc,
                    'author'      => $series->author,
                    'narrator'    => $series->narrator,
                    'episodes'    => $episodes,
                ],
            ]);

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
