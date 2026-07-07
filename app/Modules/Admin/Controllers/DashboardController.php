<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Order;
use App\Models\Series;
use App\Models\User;
use App\Modules\Admin\Support\AdminPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function index(): JsonResponse
    {
        $paidRevenue = (float) Order::query()
            ->where('status', 'paid')
            ->sum('amount');

        $recentSeries = Series::query()
            ->withCount('episodes')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'cover_url', 'author', 'category', 'total_episodes', 'total_listens', 'is_premium', 'is_hot', 'created_at']);

        $recentComments = Comment::query()
            ->with(['user:id,username,email', 'series:id,title'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $categoryStats = Series::query()
            ->select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $recentOrders = Order::query()
            ->with(['user:id,username,email', 'plan:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return $this->success([
            'metrics' => [
                'series_count'   => Series::count(),
                'episodes_count' => Episode::count(),
                'users_count'    => User::count(),
                'orders_count'   => Order::count(),
                'paid_revenue'   => $paidRevenue,
                'comments_count' => Comment::count(),
            ],
            'recent_series'    => $recentSeries,
            'recent_comments'  => $recentComments,
            'recent_orders'    => $recentOrders->map(fn (Order $o) => AdminPresenter::order($o))->values(),
            'category_stats'   => $categoryStats,
            'orders_by_status' => Order::query()
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ]);
    }
}
