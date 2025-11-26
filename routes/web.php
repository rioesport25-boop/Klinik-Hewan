<?php

use App\Http\Controllers\Petshop\CartController as PetshopCartController;
use App\Http\Controllers\Petshop\CheckoutController as PetshopCheckoutController;
use App\Http\Controllers\Petshop\ProductController as PetshopProductController;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', function () {
    $settings = \App\Models\FooterSetting::first();
    $homeSliderImages = $settings?->home_slider_images ?? [];

    $sliders = collect($homeSliderImages)->map(function ($image) {
        return asset('storage/' . $image);
    })->toArray();

    $doctors = Doctor::active()->ordered()->get()->map(function ($doctor) {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'title' => $doctor->title,
            'specialization' => $doctor->specialization,
            'description' => $doctor->description,
            'photo_url' => $doctor->photo_path ? asset('storage/' . $doctor->photo_path) : null,
            'gradient_color' => $doctor->gradient_color,
        ];
    });

    $services = Service::active()->ordered()->get()->map(function ($service) {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'icon_url' => $service->icon_path ? asset('storage/' . $service->icon_path) : null,
            'icon_color' => $service->icon_color,
        ];
    });

    return Inertia::render('Home', [
        'sliders' => $sliders,
        'doctors' => $doctors,
        'services' => $services,
    ]);
})->name('home');

Route::get('/gallery', function () {
    $galleryImages = \App\Models\GalleryImage::active()->ordered()->get()->map(function ($image) {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'description' => $image->description,
            'image_url' => $image->image_path ? asset('storage/' . $image->image_path) : null,
        ];
    });

    $settings = \App\Models\FooterSetting::first();
    $parallaxBg = $settings?->gallery_parallax_bg;

    return Inertia::render('Gallery', [
        'galleryImages' => $galleryImages,
        'parallaxBackground' => $parallaxBg ? asset('storage/' . $parallaxBg) : null,
    ]);
})->name('gallery');

Route::get('/blog', function () {
    $settings = \App\Models\FooterSetting::first();
    $blogHeaderImage = $settings?->blog_header_image;

    $posts = BlogPost::published()->latest()->get()->map(function ($post) {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'featured_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
            'author' => $post->author,
            'published_at' => $post->formatted_date,
            'read_time' => $post->read_time,
            'views' => $post->views,
        ];
    });

    return Inertia::render('Blog', [
        'posts' => $posts,
        'headerImage' => $blogHeaderImage ? asset('storage/' . $blogHeaderImage) : null,
    ]);
})->name('blog');

Route::get('/doctor-schedule', function () {
    // Get current week dates (Asia/Jakarta timezone)
    $today = now()->timezone('Asia/Jakarta');
    $currentDayOfWeek = $today->dayOfWeek; // 0 = Sunday, 1 = Monday, ...
    
    // Get Monday of current week
    if ($currentDayOfWeek === 0) {
        // If Sunday, go back to previous Monday
        $monday = $today->copy()->subDays(6)->startOfDay();
    } else {
        // Otherwise get Monday of this week
        $monday = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->startOfDay();
    }
    
    // Generate week dates
    $weekDates = [];
    $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $dayNamesIndo = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];
    
    // Get holidays in current week
    $weekStart = $monday->copy()->startOfDay();
    $weekEnd = $monday->copy()->addDays(6)->endOfDay();
    $holidays = \App\Models\Holiday::active()
        ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
        ->get()
        ->keyBy(fn($h) => $h->date->format('Y-m-d'));
    
    for ($i = 0; $i < 7; $i++) {
        $date = $monday->copy()->addDays($i);
        $dateString = $date->format('Y-m-d');
        $holiday = $holidays->get($dateString);
        
        $weekDates[] = [
            'dayKey' => $dayOrder[$i],
            'dayName' => $dayNamesIndo[$dayOrder[$i]],
            'dayNumber' => $date->day,
            'month' => $date->month,
            'monthName' => $date->locale('id')->translatedFormat('M'),
            'year' => $date->year,
            'isToday' => $date->isSameDay($today),
            'fullDate' => $dateString,
            'isHoliday' => $holiday !== null,
            'holiday' => $holiday ? [
                'id' => $holiday->id,
                'name' => $holiday->name,
                'description' => $holiday->description,
                'type' => $holiday->type,
                'color' => $holiday->color,
            ] : null,
        ];
    }
    
    // Get week range
    $weekStart = $monday->copy();
    $weekEnd = $monday->copy()->addDays(6);
    $weekRange = $weekStart->day . ' ' . $weekStart->locale('id')->translatedFormat('F') . ' - ' . 
                 $weekEnd->day . ' ' . $weekEnd->locale('id')->translatedFormat('F Y');
    
    // Get all active doctors with their active schedules
    $doctors = Doctor::active()
        ->ordered()
        ->with(['schedules' => function ($query) {
            $query->active()->ordered();
        }])
        ->get()
        ->map(function ($doctor) {
            $schedules = $doctor->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'day_name' => $schedule->day_name,
                    'start_time' => $schedule->start_time->format('H:i'),
                    'end_time' => $schedule->end_time->format('H:i'),
                    'notes' => $schedule->notes,
                ];
            });

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'title' => $doctor->title,
                'specialization' => $doctor->specialization,
                'description' => $doctor->description,
                'photo_url' => $doctor->photo_path ? asset('storage/' . $doctor->photo_path) : null,
                'gradient_color' => $doctor->gradient_color,
                'schedules' => $schedules,
            ];
        });

    return Inertia::render('DoctorSchedule', [
        'doctors' => $doctors,
        'currentWeek' => $weekDates,
        'weekRange' => $weekRange,
        'today' => $today->toDateString(),
    ]);
})->name('doctor-schedule');

// Booking Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->prefix('booking')->name('booking.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BookingController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\BookingController::class, 'store'])->name('store');
    Route::get('/slots', [\App\Http\Controllers\BookingController::class, 'getAvailableSlots'])->name('slots');
    Route::get('/success/{bookingCode}', [\App\Http\Controllers\BookingController::class, 'success'])->name('success');
    Route::get('/history', [\App\Http\Controllers\BookingController::class, 'history'])->name('history');
    Route::get('/detail/{bookingCode}', [\App\Http\Controllers\BookingController::class, 'detail'])->name('detail');
    Route::post('/{appointment}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('cancel');
    Route::post('/{appointment}/review', [\App\Http\Controllers\BookingController::class, 'review'])->name('review');
});

Route::get('/blog/{slug}', function ($slug) {
    $post = BlogPost::where('slug', $slug)->published()->firstOrFail();

    // Increment views
    $post->incrementViews();

    // Get related posts (same category or recent)
    $relatedPosts = BlogPost::published()
        ->where('id', '!=', $post->id)
        ->latest()
        ->limit(3)
        ->get()
        ->map(function ($relatedPost) {
            return [
                'id' => $relatedPost->id,
                'title' => $relatedPost->title,
                'slug' => $relatedPost->slug,
                'excerpt' => $relatedPost->excerpt,
                'featured_image' => $relatedPost->featured_image ? asset('storage/' . $relatedPost->featured_image) : null,
                'published_at' => $relatedPost->formatted_date,
                'read_time' => $relatedPost->read_time,
            ];
        });

    return Inertia::render('BlogShow', [
        'post' => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'featured_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
            'author' => $post->author,
            'published_at' => $post->formatted_date,
            'read_time' => $post->read_time,
            'views' => $post->views,
        ],
        'relatedPosts' => $relatedPosts,
    ]);
})->name('blog.show');

// Petshop Routes
Route::prefix('petshop')->name('petshop.')->group(function () {
    // Public routes - dapat diakses tanpa login
    Route::get('/', [PetshopProductController::class, 'index'])->name('index');
    Route::get('/products/{slug}', [PetshopProductController::class, 'show'])->name('product.show');

    // Protected routes - harus login
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
        Route::get('/cart', [PetshopCartController::class, 'show'])->name('cart.show');
        Route::post('/cart/items', [PetshopCartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [PetshopCartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [PetshopCartController::class, 'destroy'])->name('cart.items.destroy');
        Route::post('/cart/remove-multiple', [PetshopCartController::class, 'removeMultiple'])->name('cart.removeMultiple');
        Route::post('/cart/clear', [PetshopCartController::class, 'clear'])->name('cart.clear');

        Route::post('/cart/checkout', [PetshopCartController::class, 'checkout'])->name('cart.checkout');

        Route::get('/payment/status', [PetshopCartController::class, 'paymentStatus'])->name('payment.status');
    });

    // Payment callback routes - tidak perlu auth karena dipanggil dari Midtrans
    Route::get('/payment/finish', [\App\Http\Controllers\Petshop\MidtransController::class, 'finish'])->name('payment.finish');
    Route::get('/payment/unfinish', [\App\Http\Controllers\Petshop\MidtransController::class, 'unfinish'])->name('payment.unfinish');
    Route::get('/payment/error', [\App\Http\Controllers\Petshop\MidtransController::class, 'error'])->name('payment.error');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/addresses', [\App\Http\Controllers\Profile\AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [\App\Http\Controllers\Profile\AddressController::class, 'store'])->name('addresses.store');
        Route::patch('/addresses/{address}', [\App\Http\Controllers\Profile\AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Profile\AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/set-default', [\App\Http\Controllers\Profile\AddressController::class, 'setDefault'])->name('addresses.set-default');

        Route::get('/favorites', [\App\Http\Controllers\Profile\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{product}/toggle', [\App\Http\Controllers\Profile\FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::delete('/favorites/{favorite}', [\App\Http\Controllers\Profile\FavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::get('/transactions', [\App\Http\Controllers\Profile\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{order}', [\App\Http\Controllers\Profile\TransactionController::class, 'show'])->name('transactions.show');

        Route::patch('/pets/{pet}', [\App\Http\Controllers\Profile\PetController::class, 'update'])->name('pets.update');
        Route::delete('/pets/{pet}', [\App\Http\Controllers\Profile\PetController::class, 'destroy'])->name('pets.destroy');
    });

    // Notifications Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('unread');
        Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});
