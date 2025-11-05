<?php

namespace App\Http\Middleware;

use App\Models\FooterSetting;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $footerSettings = FooterSetting::getSettings();
        $cartSummary = $this->cartService->transformCart(
            $this->cartService->getCart(createIfMissing: false, withRelations: true),
            summary: true
        );

        return [
            ...parent::share($request),
            'appLogo' => $footerSettings->logo ? asset('storage/' . $footerSettings->logo) : null,
            'appLogoDark' => $footerSettings->logo_dark ? asset('storage/' . $footerSettings->logo_dark) : null,
            'footerSettings' => [
                'about_text' => $footerSettings->about_text,
                'contact_phone' => $footerSettings->contact_phone,
                'contact_email' => $footerSettings->contact_email,
                'contact_address' => $footerSettings->contact_address,
                'google_maps_iframe' => $footerSettings->google_maps_iframe,
                'instagram_url' => $footerSettings->instagram_url,
                'facebook_url' => $footerSettings->facebook_url,
                'tiktok_url' => $footerSettings->tiktok_url,
                'youtube_url' => $footerSettings->youtube_url,
                'whatsapp_number' => $footerSettings->whatsapp_number ?? null,
            ],
            'cartSummary' => $cartSummary,
        ];
    }
}
