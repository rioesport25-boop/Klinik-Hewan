<?php

use App\Http\Controllers\Petshop\CartController as PetshopCartController;
use App\Http\Controllers\Petshop\CheckoutController as PetshopCheckoutController;
use App\Http\Controllers\Petshop\ProductController as PetshopProductController;
use App\Models\Doctor;
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
    Route::get('/', [PetshopProductController::class, 'index'])->name('index');
    Route::get('/products/{slug}', [PetshopProductController::class, 'show'])->name('product.show');

    Route::get('/cart', [PetshopCartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [PetshopCartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [PetshopCartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [PetshopCartController::class, 'destroy'])->name('cart.items.destroy');
    Route::post('/cart/clear', [PetshopCartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [PetshopCheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [PetshopCheckoutController::class, 'store'])->name('checkout.store');
    
    // Payment callback routes
    Route::get('/payment/finish', [\App\Http\Controllers\Petshop\MidtransController::class, 'finish'])->name('payment.finish');
    Route::get('/payment/unfinish', [\App\Http\Controllers\Petshop\MidtransController::class, 'unfinish'])->name('payment.unfinish');
    Route::get('/payment/error', [\App\Http\Controllers\Petshop\MidtransController::class, 'error'])->name('payment.error');
    Route::get('/payment/status', [PetshopCheckoutController::class, 'paymentStatus'])->name('payment.status');
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
        Route::get('/addresses', [\App\Http\Controllers\Profile\AddressController::class, 'index'])->name('addresses');
        Route::post('/addresses', [\App\Http\Controllers\Profile\AddressController::class, 'store'])->name('addresses.store');
        Route::patch('/addresses/{address}', [\App\Http\Controllers\Profile\AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Profile\AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/set-default', [\App\Http\Controllers\Profile\AddressController::class, 'setDefault'])->name('addresses.set-default');
        
        Route::get('/favorites', [\App\Http\Controllers\Profile\FavoriteController::class, 'index'])->name('favorites');
    });
});
