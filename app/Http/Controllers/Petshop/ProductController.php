<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display the product catalogue.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
            'sort' => $request->string('sort')->toString() ?: 'newest',
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
        ];

        $productsQuery = Product::query()
            ->with([
                'category:id,name,slug',
                'images' => function ($query) {
                    $query->orderByDesc('is_primary')->orderBy('order');
                },
            ])
            ->withCount([
                'variants as active_variants_count' => function (Builder $query) {
                    $query->where('is_active', true);
                },
            ])
            ->where('is_active', true);

        if ($filters['search']) {
            $search = $filters['search'];
            $productsQuery->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('specifications', 'like', '%' . $search . '%');
            });
        }

        if ($filters['category']) {
            $productsQuery->whereHas('category', function (Builder $query) use ($filters) {
                $query->where('slug', $filters['category'])
                    ->orWhere('id', $filters['category']);
            });
        }

        if (is_numeric($filters['price_min'])) {
            $productsQuery->where('price', '>=', (float) $filters['price_min']);
        }

        if (is_numeric($filters['price_max'])) {
            $productsQuery->where('price', '<=', (float) $filters['price_max']);
        }

        switch ($filters['sort']) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'popular':
                $productsQuery->orderBy('order_count', 'desc');
                break;
            case 'rating':
                $productsQuery->orderBy('rating_average', 'desc');
                break;
            default:
                $productsQuery->latest('created_at');
                break;
        }

        $products = $productsQuery
            ->paginate(12)
            ->withQueryString()
            ->through(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                    'discount_percentage' => $product->discount_percentage,
                    'stock' => (int) $product->stock,
                    'is_featured' => (bool) $product->is_featured,
                    'rating_average' => (float) $product->rating_average,
                    'review_count' => (int) $product->review_count,
                    'order_count' => (int) $product->order_count,
                    'has_variants' => $product->active_variants_count > 0,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'primary_image_url' => optional($product->primary_image)->image_url,
                ];
            });

        $categories = ProductCategory::query()
            ->active()
            ->ordered()
            ->withCount(['products' => function (Builder $query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function (ProductCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'product_count' => $category->products_count,
                ];
            });

        $priceBounds = Product::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(MIN(price), 0) as min_price, COALESCE(MAX(price), 0) as max_price')
            ->first();

        $settings = \App\Models\FooterSetting::first();
        $petshopHeaderImages = $settings?->petshop_header_images ?? [];

        // Convert paths to full URLs
        $headerImagesUrls = collect($petshopHeaderImages)->map(function ($image) {
            return asset('storage/' . $image);
        })->toArray();

        return Inertia::render('Petshop/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'priceRange' => [
                'min' => (float) ($priceBounds->min_price ?? 0),
                'max' => (float) ($priceBounds->max_price ?? 0),
            ],
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Terbaru'],
                ['value' => 'price_asc', 'label' => 'Harga Terendah'],
                ['value' => 'price_desc', 'label' => 'Harga Tertinggi'],
                ['value' => 'popular', 'label' => 'Terlaris'],
                ['value' => 'rating', 'label' => 'Rating Tertinggi'],
            ],
            'headerImages' => $headerImagesUrls,
        ]);
    }

    /**
     * Display the given product detail page.
     */
    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'category:id,name,slug',
                'images' => function ($query) {
                    $query->orderByDesc('is_primary')->orderBy('order');
                },
                'variants' => function ($query) {
                    $query->orderBy('price_adjustment');
                },
                'reviews' => function ($query) {
                    $query->approved()
                        ->with('user:id,name')
                        ->latest();
                },
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'specifications' => $product->specifications,
            'price' => (float) $product->price,
            'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
            'discount_percentage' => $product->discount_percentage,
            'weight' => $product->weight ? (float) $product->weight : null,
            'stock' => (int) $product->stock,
            'is_featured' => (bool) $product->is_featured,
            'rating_average' => (float) $product->rating_average,
            'review_count' => (int) $product->review_count,
            'order_count' => (int) $product->order_count,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->image_url,
                    'is_primary' => (bool) $image->is_primary,
                ];
            })->values(),
            'variants' => $product->variants->map(function (ProductVariant $variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'sku' => $variant->sku,
                    'stock' => $variant->stock,
                    'is_active' => (bool) $variant->is_active,
                    'price_adjustment' => (float) $variant->price_adjustment,
                    'final_price' => (float) $variant->final_price,
                ];
            })->filter(fn($variant) => $variant['is_active'])->values(),
            'reviews' => $product->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'review' => $review->review,
                    'user' => $review->user ? [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ] : [
                        'name' => 'Pelanggan',
                    ],
                    'created_at' => optional($review->created_at)->diffForHumans(),
                ];
            }),
        ];

        $relatedProducts = Product::query()
            ->with([
                'images' => function ($query) {
                    $query->orderByDesc('is_primary')->orderBy('order');
                },
                'category:id,name,slug',
            ])
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function (Product $related) {
                return [
                    'id' => $related->id,
                    'name' => $related->name,
                    'slug' => $related->slug,
                    'price' => (float) $related->price,
                    'compare_price' => $related->compare_price ? (float) $related->compare_price : null,
                    'discount_percentage' => $related->discount_percentage,
                    'primary_image_url' => optional($related->primary_image)->image_url,
                    'category' => $related->category ? [
                        'id' => $related->category->id,
                        'name' => $related->category->name,
                        'slug' => $related->category->slug,
                    ] : null,
                ];
            });

        return Inertia::render('Petshop/Products/Show', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
