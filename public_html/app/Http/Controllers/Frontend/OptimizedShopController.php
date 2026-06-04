<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Commerce\Models\Product;
use Modules\Commerce\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizedShopController extends Controller
{
    // Cache duration in minutes
    const CACHE_DURATION = 60;
    
    // Cache keys
    const CACHE_KEYS = [
        'featured_products' => 'featured_products',
        'new_products' => 'new_products',
        'categories' => 'categories',
        'trending_products' => 'trending_products',
        'product_count' => 'product_count'
    ];

    public function index(Request $request)
    {
        $cacheKey = 'shop_page_' . md5($request->fullUrl());
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($request) {
            $products = $this->getProducts($request);
            $filters = $this->getFilters();
            
            return view('frontend.shop.index', compact('products', 'filters'));
        });
    }

    private function getProducts(Request $request)
    {
        $query = Product::active()
            ->with(['category', 'brand'])
            ->select([
                'id', 'name_ar', 'name_en', 'slug', 'main_image', 'main_image_webp',
                'b2c_price', 'discount_percentage', 'category_id', 'brand_id',
                'stock_quantity', 'is_featured', 'is_new', 'created_at'
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Optimize query with proper indexing
        return $query->orderBy('created_at', 'desc')
            ->paginate(24)
            ->appends($request->query());
    }

    private function applyFilters($query, Request $request)
    {
        // Category filter
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Brand filter
        if ($request->brand) {
            $query->where('brand_id', $request->brand);
        }

        // Price range filter
        if ($request->min_price) {
            $query->where('b2c_price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('b2c_price', '<=', $request->max_price);
        }

        // Featured filter
        if ($request->featured) {
            $query->where('is_featured', true);
        }

        // New products filter
        if ($request->new) {
            $query->where('is_new', true);
        }

        // In stock filter
        if ($request->in_stock) {
            $query->where('stock_quantity', '>', 0);
        }

        // Search filter
        if ($request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name_ar', 'like', $searchTerm)
                  ->orWhere('name_en', 'like', $searchTerm)
                  ->orWhere('description_ar', 'like', $searchTerm)
                  ->orWhere('description_en', 'like', $searchTerm);
            });
        }

        // Sorting
        $this->applySorting($query, $request);
    }

    private function applySorting($query, Request $request)
    {
        $sort = $request->get('sort', 'created_at_desc');

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('b2c_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('b2c_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name_ar', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name_ar', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    private function getFilters()
    {
        return Cache::remember('shop_filters', self::CACHE_DURATION, function () {
            return [
                'categories' => Category::active()
                    ->withCount('products')
                    ->orderBy('name_ar')
                    ->get(),
                'brands' => Product::active()
                    ->select('brand_id', DB::raw('count(*) as count'))
                    ->groupBy('brand_id')
                    ->get(),
                'price_ranges' => $this->getPriceRanges(),
            ];
        });
    }

    private function getPriceRanges()
    {
        return [
            ['min' => 0, 'max' => 50, 'label' => 'أقل من 50 ريال'],
            ['min' => 50, 'max' => 100, 'label' => '50-100 ريال'],
            ['min' => 100, 'max' => 200, 'label' => '100-200 ريال'],
            ['min' => 200, 'max' => 500, 'label' => '200-500 ريال'],
            ['min' => 500, 'max' => null, 'label' => 'أكثر من 500 ريال'],
        ];
    }

    public function searchAjax(Request $request)
    {
        $query = $request->get('q');
        $limit = min($request->get('limit', 10), 20);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'search_' . md5($query . $limit);
        
        $results = Cache::remember($cacheKey, 30, function () use ($query, $limit) {
            return Product::active()
                ->select([
                    'id', 'name_ar', 'name_en', 'slug', 'main_image', 'main_image_webp', 'b2c_price'
                ])
                ->where(function ($q) use ($query) {
                    $searchTerm = '%' . $query . '%';
                    $q->where('name_ar', 'like', $searchTerm)
                      ->orWhere('name_en', 'like', $searchTerm);
                })
                ->orderBy('name_ar')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name_ar,
                        'url' => route('product.show', $product->slug),
                        'image' => $product->main_image,
                        'price' => number_format($product->b2c_price, 2) . ' ₪',
                    ];
                });
        });

        return response()->json($results);
    }

    public function getFeaturedProducts()
    {
        return Cache::remember(self::CACHE_KEYS['featured_products'], self::CACHE_DURATION, function () {
            return Product::active()
                ->featured()
                ->select(['id', 'name_ar', 'name_en', 'slug', 'main_image', 'b2c_price'])
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();
        });
    }

    public function getNewProducts()
    {
        return Cache::remember(self::CACHE_KEYS['new_products'], self::CACHE_DURATION, function () {
            return Product::active()
                ->new()
                ->select(['id', 'name_ar', 'name_en', 'slug', 'main_image', 'b2c_price'])
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();
        });
    }

    public function getProductCount()
    {
        return Cache::remember(self::CACHE_KEYS['product_count'], self::CACHE_DURATION, function () {
            return Product::active()->count();
        });
    }

    // Clear cache when products are updated
    public static function clearProductCache()
    {
        $keys = array_values(self::CACHE_KEYS);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // Clear search cache
        $searchKeys = Cache::get('search_keys', []);
        foreach ($searchKeys as $key) {
            Cache::forget($key);
        }
        
        // Clear shop page cache
        $shopCacheKeys = Cache::get('shop_cache_keys', []);
        foreach ($shopCacheKeys as $key) {
            Cache::forget($key);
        }
    }

    // Cache warming for better performance
    public static function warmCache()
    {
        $controller = new self();
        
        // Warm common caches
        $controller->getFeaturedProducts();
        $controller->getNewProducts();
        $controller->getProductCount();
        
        // Warm search cache for common terms
        $commonSearches = ['كريم', 'زيت', 'شامبو', 'مكياج'];
        foreach ($commonSearches as $search) {
            $cacheKey = 'search_' . md5($search . 10);
            Cache::remember($cacheKey, 30, function () use ($search) {
                return Product::active()
                    ->where('name_ar', 'like', '%' . $search . '%')
                    ->limit(10)
                    ->get(['id', 'name_ar', 'slug', 'main_image', 'b2c_price']);
            });
        }
    }
}
