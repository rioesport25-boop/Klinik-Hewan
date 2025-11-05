<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->status,
                    'minutes' => ceil($exception->status / 60),
                ]))
                ->body(array_values($exception->errors())[0][0] ?? null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        // Attempt to authenticate
        if (!auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['remember'] ?? false)) {

            // Login credentials salah
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        // Check if user is admin
        $user = auth()->user();

        if ($user->role !== 'admin') {
            // Logout non-admin user
            auth()->logout();

            // Show error message
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya admin yang dapat mengakses halaman ini. Silakan gunakan akun admin untuk login.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Akun ini tidak memiliki akses admin.',
            ]);
        }

        // Call parent authenticate to complete the login process
        return app(LoginResponse::class);
    }
}
