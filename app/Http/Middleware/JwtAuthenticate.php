<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\ResolvesJwtUser;
use App\Models\User;
use App\Modules\Auth\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    use ResolvesJwtUser;

    public function __construct(protected JwtService $jwtService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractBearerToken($request);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userId = $this->jwtService->userIdFromToken($token);

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại.',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
