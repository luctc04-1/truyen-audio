<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\ResolvesJwtUser;
use App\Modules\Auth\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtOptionalAuthenticate
{
    use ResolvesJwtUser;

    public function __construct(protected JwtService $jwtService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->resolveUserFromBearer($request);

        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
