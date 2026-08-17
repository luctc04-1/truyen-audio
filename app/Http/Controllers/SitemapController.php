<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Series;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap for Search Engines
     */
    public function index(): Response
    {
        $baseUrl = url('/');
        $seriesList = Series::select(['id', 'slug', 'title', 'updated_at', 'created_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Static Pages
        $staticPages = [
            '/'          => ['priority' => '1.0', 'changefreq' => 'daily'],
            '/library'   => ['priority' => '0.8', 'changefreq' => 'daily'],
            '/community' => ['priority' => '0.6', 'changefreq' => 'daily'],
            '/vip'       => ['priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $now = date('c');
        foreach ($staticPages as $path => $meta) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($baseUrl . $path) . '</loc>';
            $xml[] = '    <lastmod>' . $now . '</lastmod>';
            $xml[] = '    <changefreq>' . $meta['changefreq'] . '</changefreq>';
            $xml[] = '    <priority>' . $meta['priority'] . '</priority>';
            $xml[] = '  </url>';
        }

        // 2. Series Pages
        foreach ($seriesList as $series) {
            $slugPart = $series->slug ? '-' . $series->slug : '';
            $storyUrl = $baseUrl . '/story/' . $series->id . $slugPart;
            $lastmod = ($series->updated_at ?? $series->created_at ?? now())->toIso8601String();

            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($storyUrl) . '</loc>';
            $xml[] = '    <lastmod>' . $lastmod . '</lastmod>';
            $xml[] = '    <changefreq>weekly</changefreq>';
            $xml[] = '    <priority>0.9</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return response(implode("\n", $xml), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
