<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class FilamentLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Get the intended URL or default to Filament dashboard
        $intendedUrl = session()->pull('url.intended', Filament::getUrl());

        return redirect()->to($intendedUrl);
    }
}
