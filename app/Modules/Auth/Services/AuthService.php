<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected JwtService $jwtService,
        protected GoogleAuthService $googleAuthService,
    ) {}

    /**
     * @return array{token: string, user: array<string, mixed>}
     */
    public function loginWithCredentials(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || empty($user->password) || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        return $this->authResponse($user);
    }

    /**
     * @param  array{username: string, email: string, password: string}  $data
     * @return array{token: string, user: array<string, mixed>}
     */
    public function register(array $data): array
    {
        $user = User::create($this->newUserAttributes([
            'username'   => $data['username'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'avatar_url' => null,
            'is_admin'   => false,
        ]));

        return $this->authResponse($user);
    }

    /**
     * @return array{token: string, user: array<string, mixed>}
     */
    public function loginWithGoogle(string $idToken): array
    {
        $payload = $this->googleAuthService->verifyIdToken($idToken);
        $user = $this->findOrCreateGoogleUser($payload);

        return $this->authResponse($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function formatUser(User $user): array
    {
        $subscription = $user->activeSubscription();

        return [
            'id'           => $user->id,
            'username'     => $user->username,
            'email'        => $user->email,
            'avatar_url'   => $user->avatar_url,
            'is_admin'     => (bool) $user->is_admin,
            'is_premium'   => $subscription !== null,
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan?->name,
                'start_at'  => $subscription->start_at?->toIso8601String(),
                'end_at'    => $subscription->end_at?->toIso8601String(),
            ] : null,
        ];
    }

    /**
     * @param  array{sub: string, email: string, name: ?string, picture: ?string}  $payload
     */
    protected function findOrCreateGoogleUser(array $payload): User
    {
        $user = User::where('google_id', $payload['sub'])->first();

        if ($user) {
            return $this->syncGoogleProfile($user, $payload);
        }

        $user = User::where('email', $payload['email'])->first();

        if ($user) {
            $user->google_id = $payload['sub'];

            return $this->syncGoogleProfile($user, $payload);
        }

        return User::create($this->newUserAttributes([
            'google_id'  => $payload['sub'],
            'email'      => $payload['email'],
            'username'   => $this->resolveUsername($payload),
            'avatar_url' => $payload['picture'] ?? null,
            'password'   => Hash::make(Str::random(40)),
            'is_admin'   => false,
        ]));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function newUserAttributes(array $attributes): array
    {
        return array_merge($attributes, [
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array{email: string, name: ?string, picture: ?string}  $payload
     */
    protected function syncGoogleProfile(User $user, array $payload): User
    {
        $user->fill([
            'email'      => $payload['email'],
            'avatar_url' => $payload['picture'] ?? $user->avatar_url,
        ]);

        if (empty($user->username) && !empty($payload['name'])) {
            $user->username = $payload['name'];
        }

        $user->save();

        return $user->fresh();
    }

    /**
     * @param  array{email: string, name: ?string}  $payload
     */
    protected function resolveUsername(array $payload): string
    {
        $base = trim((string) ($payload['name'] ?? ''));

        if ($base === '') {
            $base = Str::before($payload['email'], '@');
        }

        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.' '.$suffix;
            $suffix++;
        }

        return $username;
    }

    /**
     * @return array{token: string, user: array<string, mixed>}
     */
    protected function authResponse(User $user): array
    {
        return [
            'token' => $this->jwtService->issueToken($user),
            'user'  => $this->formatUser($user),
        ];
    }
}
