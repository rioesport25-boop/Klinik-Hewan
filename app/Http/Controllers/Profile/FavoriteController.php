<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    /**
     * Display user's favorite products
     */
    public function index()
    {
        $favorites = auth()->user()
            ->favorites()
            ->with(['product.category', 'product.images'])
            ->latest()
            ->get()
            ->map(fn($favorite) => [
                'id' => $favorite->id,
                'product' => [
                    'id' => $favorite->product->id,
                    'name' => $favorite->product->name,
                    'slug' => $favorite->product->slug,
                    'price' => $favorite->product->price,
                    'stock' => $favorite->product->stock,
                    'category' => $favorite->product->category ? [
                        'name' => $favorite->product->category->name,
                    ] : null,
                    'image' => $favorite->product->images->first()
                        ? asset('storage/' . $favorite->product->images->first()->image_path)
                        : null,
                ],
                'created_at' => $favorite->created_at->format('d M Y'),
            ]);

        return Inertia::render('Profile/Favorites/Index', [
            'favorites' => $favorites,
        ]);
    }

    /**
     * Toggle favorite status for a product
     */
    public function toggle(Product $product)
    {
        $favorite = UserFavorite::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Produk dihapus dari favorit';
            $isFavorited = false;
        } else {
            UserFavorite::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            $message = 'Produk ditambahkan ke favorit';
            $isFavorited = true;
        }

        if (request()->wantsJson()) {
            return response()->json([
                'message' => $message,
                'is_favorited' => $isFavorited,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a product from favorites
     */
    public function destroy(UserFavorite $favorite)
    {
        // Check if user owns this favorite
        if ($favorite->user_id !== auth()->id()) {
            abort(403);
        }

        $favorite->delete();

        return redirect()->route('profile.favorites.index')
            ->with('success', 'Produk berhasil dihapus dari favorit');
    }
}
