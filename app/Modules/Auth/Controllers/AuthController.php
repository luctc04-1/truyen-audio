<?php

namespace App\Modules\Auth\Controllers;

use App\Shared\Controllers\BaseController;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use UnexpectedValueException;

class AuthController extends BaseController
{
    public function __construct(protected AuthService $authService) {}

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $payload = $this->authService->loginWithCredentials(
                $credentials['email'],
                $credentials['password']
            );

            return $this->success($payload, 'Đăng nhập thành công');
        } catch (ValidationException $e) {
            return $this->error(
                collect($e->errors())->flatten()->first() ?? 'Đăng nhập thất bại',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error('Đăng nhập thất bại', 401);
        }
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'username' => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $payload = $this->authService->register($data);

            return $this->success($payload, 'Đăng ký thành công', 201);
        } catch (ValidationException $e) {
            return $this->error(
                collect($e->errors())->flatten()->first() ?? 'Đăng ký thất bại',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error('Đăng ký thất bại', 400);
        }
    }

    public function google(Request $request)
    {
        try {
            $request->validate([
                'id_token' => 'required|string',
            ]);

            $payload = $this->authService->loginWithGoogle($request->input('id_token'));

            return $this->success($payload, 'Đăng nhập Google thành công');
        } catch (ValidationException $e) {
            return $this->error(
                collect($e->errors())->flatten()->first() ?? 'Xác thực Google thất bại',
                422,
                $e->errors()
            );
        } catch (InvalidArgumentException|UnexpectedValueException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (QueryException $e) {
            Log::error('Google login database error', ['message' => $e->getMessage()]);

            return $this->error($this->resolveGoogleErrorMessage($e), 401);
        } catch (\Throwable $e) {
            Log::error('Google login failed', ['message' => $e->getMessage(), 'exception' => $e::class]);

            return $this->error($this->resolveGoogleErrorMessage($e), 401);
        }
    }

    protected function resolveGoogleErrorMessage(\Throwable $e): string
    {
        if (config('app.debug')) {
            return $e->getMessage();
        }

        return 'Đăng nhập Google thất bại';
    }

    public function me(Request $request)
    {
        return $this->success($this->userWithStats($request->user()), 'Success');
    }

    public function update(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $data = $request->validate([
                'username'   => 'sometimes|string|max:255|unique:users,username,'.$userId,
                'avatar_url' => 'sometimes|nullable|url|max:500',
            ]);

            $request->user()->update($data);

            return $this->success($this->userWithStats($request->user()->fresh()), 'Cập nhật thành công');
        } catch (ValidationException $e) {
            return $this->error(
                collect($e->errors())->flatten()->first() ?? 'Cập nhật thất bại',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error('Cập nhật thất bại', 400);
        }
    }

    public function history(Request $request)
    {
        $items = $request->user()
            ->listeningHistories()
            ->with([
                'episode:id,series_id,title,episode_number,duration_seconds',
                'episode.series:id,title,cover_url,is_complete,total_episodes',
            ])
            ->orderByDesc('last_listened_at')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $episode  = $item->episode;
                $series   = $episode?->series;
                $duration = $episode?->duration_seconds ?? 0;
                $progress = $duration > 0
                    ? min(100, (int) round(($item->listened_seconds / $duration) * 100))
                    : ($item->completed ? 100 : 0);

                return [
                    'id'               => $item->id,
                    'series_id'        => $series?->id,
                    'series_title'     => $series?->title,
                    'cover_url'        => $series?->cover_url,
                    'episode_number'   => $episode?->episode_number,
                    'episode_title'    => $episode?->title,
                    'listened_seconds' => $item->listened_seconds,
                    'duration_seconds' => $duration,
                    'progress'         => $progress,
                    'completed'        => $item->completed,
                    'last_listened_at' => $item->last_listened_at?->toIso8601String(),
                ];
            });

        return $this->success(['items' => $items], 'Lịch sử nghe');
    }

    public function recordProgress(Request $request)
    {
        try {
            $data = $request->validate([
                'episode_id'       => 'required|uuid|exists:episodes,id',
                'listened_seconds' => 'required|integer|min:0',
                'completed'        => 'boolean',
            ]);

            $userId    = $request->user()->id;
            $episodeId = $data['episode_id'];
            $listened  = $data['listened_seconds'];
            $completed = $data['completed'] ?? false;

            // Dùng upsert với GREATEST để chỉ tăng listened_seconds, không bao giờ giảm.
            // completed một khi là true thì giữ nguyên.
            DB::statement("
                INSERT INTO listening_histories (id, user_id, episode_id, listened_seconds, completed, last_listened_at)
                VALUES (gen_random_uuid(), ?, ?, ?, ?, now())
                ON CONFLICT (user_id, episode_id)
                DO UPDATE SET
                    listened_seconds = GREATEST(listening_histories.listened_seconds, EXCLUDED.listened_seconds),
                    completed        = listening_histories.completed OR EXCLUDED.completed,
                    last_listened_at = now()
            ", [$userId, $episodeId, $listened, $completed]);

            return $this->success(null, 'Đã lưu tiến trình');
        } catch (ValidationException $e) {
            return $this->error(
                collect($e->errors())->flatten()->first() ?? 'Dữ liệu không hợp lệ',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            Log::error('Record progress failed', ['message' => $e->getMessage()]);

            return $this->error('Lưu tiến trình thất bại', 500);
        }
    }

    public function logout(Request $request)
    {
        return $this->success(null, 'Đăng xuất thành công');
    }

    private function userWithStats($user): array
    {
        $userData = $this->authService->formatUser($user);

        $userData['stats'] = [
            'episodes_listened'    => $user->listeningHistories()->count(),
            'follow_count'         => $user->favorites()->count(),
            'total_listen_seconds' => (int) $user->listeningHistories()->sum('listened_seconds'),
        ];

        return $userData;
    }
}
