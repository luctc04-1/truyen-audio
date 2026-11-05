<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Series;
use App\Modules\Series\Support\SeriesPresenter;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap with Cache and Image Sitemap support
     */
    public function index(): Response
    {
        $xmlContent = Cache::remember('dynamic_sitemap_xml', 3600, function () {
            $baseUrl = rtrim(url('/'), '/');
            $now = date('c');

            $xml = [];
            $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

            // 1. Static Pages
            $staticPages = [
                ''           => ['priority' => '1.0', 'changefreq' => 'daily'],
                '/library'   => ['priority' => '0.9', 'changefreq' => 'daily'],
                '/community' => ['priority' => '0.7', 'changefreq' => 'daily'],
                '/vip'       => ['priority' => '0.6', 'changefreq' => 'monthly'],
            ];

            foreach ($staticPages as $path => $meta) {
                $loc = $baseUrl . $path;
                $xml[] = '  <url>';
                $xml[] = '    <loc>' . htmlspecialchars($loc) . '</loc>';
                $xml[] = '    <lastmod>' . $now . '</lastmod>';
                $xml[] = '    <changefreq>' . $meta['changefreq'] . '</changefreq>';
                $xml[] = '    <priority>' . $meta['priority'] . '</priority>';
                $xml[] = '  </url>';
            }

            // 2. Category / Genre Pages
            $genres = [
                'ngon-tinh'    => 'Ngôn Tình',
                'audio-dai'    => 'Audio Dài',
                'trinh-tham'   => 'Trinh Thám',
                'giang-ho'     => 'Giang Hồ',
                'hoc-duong'    => 'Học Đường',
                'xuyen-khong'  => 'Xuyên Không',
                'roi-nuoc-mac' => 'Rơi Nước Mắt',
                'hai-huoc'     => 'Hài Hước',
                'khoa-hoc'     => 'Khoa Học',
                'kinh-di'      => 'Kinh Dị',
            ];

            foreach ($genres as $slug => $label) {
                $categoryUrl = $baseUrl . '/library?category=' . $slug;
                $xml[] = '  <url>';
                $xml[] = '    <loc>' . htmlspecialchars($categoryUrl) . '</loc>';
                $xml[] = '    <lastmod>' . $now . '</lastmod>';
                $xml[] = '    <changefreq>daily</changefreq>';
                $xml[] = '    <priority>0.8</priority>';
                $xml[] = '  </url>';
            }

            // 3. Series Pages with Image Sitemap
            $seriesList = Series::select(['id', 'slug', 'title', 'cover_url', 'updated_at', 'created_at'])
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($seriesList as $series) {
                $storyUrl = $baseUrl . '/story/' . ($series->slug ?? $series->id);
                $lastmod = ($series->updated_at ?? $series->created_at ?? now())->toIso8601String();

                $xml[] = '  <url>';
                $xml[] = '    <loc>' . htmlspecialchars($storyUrl) . '</loc>';
                $xml[] = '    <lastmod>' . $lastmod . '</lastmod>';
                $xml[] = '    <changefreq>weekly</changefreq>';
                $xml[] = '    <priority>0.9</priority>';

                if (!empty($series->cover_url)) {
                    $xml[] = '    <image:image>';
                    $xml[] = '      <image:loc>' . htmlspecialchars($series->cover_url) . '</image:loc>';
                    $xml[] = '      <image:title>' . htmlspecialchars($series->title) . '</image:title>';
                    $xml[] = '    </image:image>';
                }

                $xml[] = '  </url>';
            }

            $xml[] = '</urlset>';

            return implode("\n", $xml);
        });

        return response($xmlContent, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
