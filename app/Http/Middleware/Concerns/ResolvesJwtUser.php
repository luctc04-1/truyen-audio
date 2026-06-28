<?php

namespace App\Http\Middleware\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesJwtUser
{
    protected function extractBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }

    protected function resolveUserFromBearer(Request $request): ?User
    {
        $token = $this->extractBearerToken($request);

        if (! $token) {
            return null;
        }

        $userId = $this->jwtService->userIdFromToken($token);

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
