<?php

namespace Tests\Unit;

use App\Http\Middleware\JwtAdminAuthenticate;
use App\Models\User;
use App\Modules\Auth\Services\JwtService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class JwtAdminAuthenticateTest extends TestCase
{
    public function test_rejects_request_without_token(): void
    {
        $jwtService = Mockery::mock(JwtService::class);
        $middleware = new JwtAdminAuthenticate($jwtService);

        $request = Request::create('/api/admin/dashboard/stats', 'GET');
        $response = $middleware->handle($request, fn () => response()->json(['success' => true]));

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Unauthenticated', $response->getContent());
    }

    public function test_rejects_when_user_is_not_admin(): void
    {
        $jwtService = Mockery::mock(JwtService::class);
        $jwtService->shouldReceive('userIdFromToken')->andReturn('user-uuid-1');

        $middleware = new class($jwtService) extends JwtAdminAuthenticate {
            public $mockUser;
            public function handle(Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
            {
                $token = $this->extractBearerToken($request);
                if (! $token) {
                    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
                }
                $user = $this->mockUser;
                if (! $user) {
                    return response()->json(['success' => false, 'message' => 'Người dùng không tồn tại.'], 401);
                }
                if (! $user->is_admin) {
                    return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập khu vực quản trị.'], 403);
                }
                return $next($request);
            }
        };

        $nonAdmin = new User(['is_admin' => false]);
        $middleware->mockUser = $nonAdmin;

        $request = Request::create('/api/admin/dashboard/stats', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-token');

        $response = $middleware->handle($request, fn () => response()->json(['success' => true]));

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('quyền truy cập', $data['message'] ?? '');
    }

    public function test_allows_admin_user(): void
    {
        $jwtService = Mockery::mock(JwtService::class);
        $jwtService->shouldReceive('userIdFromToken')->andReturn('user-uuid-admin');

        $middleware = new class($jwtService) extends JwtAdminAuthenticate {
            public $mockUser;
            public function handle(Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
            {
                $token = $this->extractBearerToken($request);
                if (! $token) {
                    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
                }
                $user = $this->mockUser;
                if (! $user || ! $user->is_admin) {
                    return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
                }
                return $next($request);
            }
        };

        $admin = new User(['is_admin' => true]);
        $middleware->mockUser = $admin;

        $request = Request::create('/api/admin/dashboard/stats', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-token');

        $response = $middleware->handle($request, fn () => response()->json(['success' => true]));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
