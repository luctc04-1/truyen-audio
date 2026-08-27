<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Series;
use App\Models\Subscription;
use App\Models\User;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends BaseController
{
    public function stats(): JsonResponse
    {
        $totalSeries = Series::count();
        $totalEpisodes = Episode::count();
        $totalUsers = User::count();
        $totalComments = Comment::count();
        
        $totalRevenue = Order::whereIn('status', ['paid', 'completed'])->sum('amount');

        $activeSubscriptions = Subscription::where('end_at', '>', now())
            ->where('is_active', true)
            ->count();

        // Top 5 most listened series with rankings
        $topSeries = Series::select('id', 'title', 'category', 'cover_url', 'author', 'narrator', 'total_episodes', 'total_listens', 'listen_count', 'average_rating', 'is_premium', 'is_hot')
            ->orderByDesc('total_listens')
            ->limit(5)
            ->get();

        // Recent 5 VIP orders
        $recentOrders = Order::with(['user:id,username,email', 'plan:id,name,code'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent 5 comments
        $recentComments = Comment::with(['user:id,username,email,avatar_url', 'series:id,title,slug'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Plan sales summary with percentage
        $totalPlanRevenue = $totalRevenue > 0 ? $totalRevenue : 1;
        $planStats = Plan::withCount('orders')
            ->get()
            ->map(function ($plan) use ($totalPlanRevenue) {
                $revenue = (float) Order::where('plan_id', $plan->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->sum('amount');

                return [
                    'id' => $plan->id,
                    'code' => $plan->code,
                    'name' => $plan->name,
                    'price' => (float) $plan->price,
                    'orders_count' => $plan->orders_count,
                    'revenue' => $revenue,
                    'percentage' => round(($revenue / $totalPlanRevenue) * 100, 1),
                ];
            });

        // Top 5 categories distribution
        $categoryStats = Series::select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->selectRaw('category, count(*) as count')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($cat) use ($totalSeries) {
                $total = $totalSeries > 0 ? $totalSeries : 1;
                return [
                    'category' => $cat->category,
                    'count' => $cat->count,
                    'percentage' => round(($cat->count / $total) * 100, 1),
                ];
            });

        return $this->success([
            'metrics' => [
                'total_series' => $totalSeries,
                'total_episodes' => $totalEpisodes,
                'total_users' => $totalUsers,
                'total_comments' => $totalComments,
                'total_revenue' => (float) $totalRevenue,
                'active_subscriptions' => $activeSubscriptions,
            ],
            'top_series' => $topSeries,
            'recent_orders' => $recentOrders,
            'recent_comments' => $recentComments,
            'plan_stats' => $planStats,
            'category_stats' => $categoryStats,
        ]);
    }
}
