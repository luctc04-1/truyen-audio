<?php

namespace App\Modules\Series\Support;

use App\Models\User;

class UserPresenter
{
    public static function forPublic(User $user): array
    {
        $name = trim((string) ($user->username ?: $user->email ?: 'Người dùng'));

        return [
            'id'         => $user->id,
            'username'   => $name,
            'avatar_url' => $user->avatar_url,
            'initial'    => self::initial($name),
            'is_premium' => $user->isPremium(),
            'is_admin'   => (bool) $user->is_admin,
        ];
    }

    private static function initial(string $name): string
    {
        $char = mb_substr($name, 0, 1);

        return $char !== '' ? mb_strtoupper($char) : 'U';
    }
}
