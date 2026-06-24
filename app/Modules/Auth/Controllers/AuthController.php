<?php

namespace App\Modules\Auth\Controllers;

use App\Shared\Controllers\BaseController;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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
        return $this->success(
            $this->authService->formatUser($request->user()),
            'Success'
        );
    }

    public function logout(Request $request)
    {
        return $this->success(null, 'Đăng xuất thành công');
    }
}
