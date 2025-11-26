<?php

namespace App\Http\Middleware;

use App\Models\FooterSetting;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly CartService $cartService) {}

    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, \Closure $next)
    {
        // Skip Inertia middleware entirely for Filament admin routes
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
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
        // Skip Inertia sharing for Filament admin routes
        if ($request->is('admin') || $request->is('admin/*')) {
            return parent::share($request);
        }

        $footerSettings = FooterSetting::getSettings();
        $cartSummary = $this->cartService->transformCart(
            $this->cartService->getCart(createIfMissing: false, withRelations: true),
            summary: true
        );

        // Get user's favorite product IDs for easy checking
        $favoriteProductIds = [];
        $unreadNotificationsCount = 0;

        if ($request->user()) {
            $favoriteProductIds = $request->user()
                ->favorites()
                ->pluck('product_id')
                ->toArray();

            $unreadNotificationsCount = $request->user()
                ->unreadNotifications()
                ->count();
        }

        return [
            ...parent::share($request),
            'appLogo' => $footerSettings->logo ? asset('storage/' . $footerSettings->logo) : null,
            'appLogoDark' => $footerSettings->logo_dark ? asset('storage/' . $footerSettings->logo_dark) : null,
            'favoriteProductIds' => $favoriteProductIds,
            'unreadNotificationsCount' => $unreadNotificationsCount,
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
            'midtransClientKey' => config('services.midtrans.client_key'),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
                'snap_token' => $request->session()->get('snap_token'),
                'order_id' => $request->session()->get('order_id'),
                'order_number' => $request->session()->get('order_number'),
            ],
        ];
    }
}
