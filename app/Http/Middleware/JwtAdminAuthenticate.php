<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập khu vực quản trị.',
            ], 403);
        }

        if ($user->is_banned ?? false) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.',
            ], 403);
        }

        return $next($request);
    }
}
