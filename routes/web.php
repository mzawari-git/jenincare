<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\AffiliateController as FrontAffiliateController;
use App\Http\Controllers\Frontend\BlogController as FrontBlogController;
use App\Http\Controllers\Frontend\OptimizedImageController;
use App\Http\Controllers\Frontend\ServeStorageController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/b2b', function () {
    return view('frontend.b2b.index');
})->name('b2b');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [ContactController::class, 'newsletter'])->name('newsletter.subscribe');

Route::get('/return-policy', function () {
    return view('frontend.pages.return-policy');
})->name('return-policy');

Route::get('/shipping-policy', function () {
    return view('frontend.pages.shipping-policy');
})->name('shipping-policy');

Route::get('/faq', function () {
    return view('frontend.pages.faq');
})->name('faq');

Route::get('/terms', function () {
    return view('frontend.pages.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('frontend.pages.privacy');
})->name('privacy');

Route::get('/musk-collection', function () {
    return view('frontend.pages.musk-collection');
})->name('musk-collection');

Route::get('/affiliate', [FrontAffiliateController::class, 'landing'])->name('affiliate.landing');
Route::get('/affiliate/dashboard', [FrontAffiliateController::class, 'dashboard'])->name('affiliate.dashboard');
Route::get('/affiliate/tools', [FrontAffiliateController::class, 'marketingTools'])->name('affiliate.tools');

Route::get('/blog-tool', [\App\Http\Controllers\Frontend\BlogController::class, 'designTool'])->name('blog.design-tool');
Route::post('/blog-tool/insert', [\App\Http\Controllers\Frontend\BlogController::class, 'insertFromTool'])->name('blog.insert-tool');
Route::get('/blog', [FrontBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category}', [FrontBlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [FrontBlogController::class, 'show'])->name('blog.show');
Route::post('/affiliate/register', [FrontAffiliateController::class, 'register'])->name('affiliate.register');
Route::post('/affiliate/payout', [FrontAffiliateController::class, 'requestPayout'])->name('affiliate.payout.request');

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.submit');
Route::get('admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'checkEmail'])->name('password.email');
Route::get('security-question', [AuthController::class, 'showSecurityQuestion'])->name('password.security-question');
Route::post('security-question', [AuthController::class, 'checkSecurityAnswer'])->name('password.check-answer');
Route::get('reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'reset'])->name('password.update');

Route::prefix('auth')->group(function () {
    Route::get('{provider}/redirect', [SocialiteController::class, 'redirect'])->name('auth.redirect');
    Route::get('{provider}/callback', [SocialiteController::class, 'callback'])->name('auth.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AccountController::class, 'orderShow'])->name('orders.show');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/toggle', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::get('/account/security-question', [AuthController::class, 'showSecurityQuestionSetup'])->name('account.security-question');
    Route::post('/account/security-question', [AuthController::class, 'updateSecurityQuestion'])->name('account.security-question.update');
});

Route::get('/api/search', [ShopController::class, 'searchAjax'])->name('api.search');
Route::get('/api/product/{id}/quickview', [ProductController::class, 'quickView'])->name('api.quickview');

Route::get('/images/{path}', [OptimizedImageController::class, 'show'])->where('path', '.*')->name('image.optimized');
Route::get('/thumbnails/{path}', [OptimizedImageController::class, 'thumbnail'])->where('path', '.*');

Route::get('/storage/{path}', [ServeStorageController::class, 'show'])->where('path', '.*');
Route::get('/files/{path}', [ServeStorageController::class, 'show'])->where('path', '.*');

Route::get('/skin-admin', function () {
    return response()->file(public_path('skin-admin/index.html'));
});
Route::get('/skin-admin/{any}', function () {
    return response()->file(public_path('skin-admin/index.html'));
})->where('any', '.*');
