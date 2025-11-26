<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user is admin (has is_admin flag)
        if ($user && $user->is_admin) {
            // Admin should use /admin/login instead
            // But if they logged in via regular login, redirect to admin
            return redirect()->route('filament.admin.pages.dashboard');
        }
        
        // Clear any intended URL to prevent redirect to dashboard
        $request->session()->forget('url.intended');
        
        // Regular users always redirect to home page
        return redirect()->route('home');
    }
}
