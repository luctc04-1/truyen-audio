<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use UnexpectedValueException;

class JwtService
{
    public function issueToken(User $user): string
    {
        $now = time();
        $ttl = config('jwt.ttl', 10080) * 60;

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $now + $ttl,
            'email' => $user->email,
        ];

        return JWT::encode($payload, $this->secret(), config('jwt.algo', 'HS256'));
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret(), config('jwt.algo', 'HS256')));
    }

    public function userIdFromToken(string $token): ?string
    {
        try {
            $payload = $this->decode($token);

            return $payload->sub ?? null;
        } catch (ExpiredException|SignatureInvalidException|UnexpectedValueException|InvalidArgumentException) {
            return null;
        }
    }

    protected function secret(): string
    {
        $secret = config('jwt.secret');

        if (empty($secret)) {
            throw new InvalidArgumentException('JWT secret is not configured.');
        }

        return $secret;
    }
}
