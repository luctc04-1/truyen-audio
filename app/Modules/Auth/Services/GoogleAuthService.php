<?php

namespace App\Modules\Auth\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class GoogleAuthService
{
    protected const JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    protected const VALID_ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    /**
     * @return array{sub: string, email: string, name: ?string, picture: ?string, email_verified: bool}
     */
    public function verifyIdToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            throw new InvalidArgumentException('Google client ID is not configured.');
        }

        $parts = explode('.', $idToken);

        if (count($parts) !== 3) {
            throw new UnexpectedValueException('Invalid Google ID token format.');
        }

        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
        $kid = $header['kid'] ?? null;

        if (!$kid) {
            throw new UnexpectedValueException('Google ID token is missing key id.');
        }

        $keys = $this->getGooglePublicKeys();

        if (!isset($keys[$kid])) {
            Cache::forget('google_jwks_json');
            $keys = $this->getGooglePublicKeys();

            if (!isset($keys[$kid])) {
                throw new UnexpectedValueException('Unable to verify Google ID token signature.');
            }
        }

        try {
            $payload = JWT::decode($idToken, $keys[$kid]);
        } catch (\Throwable $e) {
            throw new UnexpectedValueException('Invalid Google ID token: '.$e->getMessage());
        }

        if (!in_array($payload->iss ?? '', self::VALID_ISSUERS, true)) {
            throw new UnexpectedValueException('Invalid Google token issuer.');
        }

        $audience = $payload->aud ?? null;

        if (!$this->audienceMatches($clientId, $audience)) {
            throw new UnexpectedValueException('Google token audience mismatch.');
        }

        if (empty($payload->email)) {
            throw new UnexpectedValueException('Google account email is required.');
        }

        if (isset($payload->email_verified) && $payload->email_verified !== true) {
            throw new UnexpectedValueException('Google email is not verified.');
        }

        return [
            'sub'             => (string) $payload->sub,
            'email'           => (string) $payload->email,
            'name'            => isset($payload->name) ? (string) $payload->name : null,
            'picture'         => isset($payload->picture) ? (string) $payload->picture : null,
            'email_verified'  => (bool) ($payload->email_verified ?? false),
        ];
    }

    protected function getGooglePublicKeys(): array
    {
        $jwks = Cache::remember('google_jwks_json', 3600, function () {
            $response = Http::timeout(10)->get(self::JWKS_URL);

            if (!$response->successful()) {
                throw new UnexpectedValueException('Unable to fetch Google public keys.');
            }

            return $response->json();
        });

        return JWK::parseKeySet($jwks);
    }

    protected function audienceMatches(string $clientId, mixed $audience): bool
    {
        if (is_array($audience)) {
            return in_array($clientId, $audience, true);
        }

        return is_string($audience) && $audience === $clientId;
    }
}
