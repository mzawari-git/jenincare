<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\B2BController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DeliveryController;
use Modules\CustomAdmin\Http\Controllers\MarketingTrackingController;
use Modules\CustomAdmin\Http\Controllers\RoasDashboardController;
use App\Http\Controllers\Admin\TriggerWordController;
use App\Http\Controllers\Admin\AiComplianceController;
use App\Http\Controllers\Admin\PredictiveController;
use App\Http\Controllers\Admin\ReviewerIpController;
use Modules\CustomAdmin\Http\Controllers\MetaAdsController;
use Modules\CustomAdmin\Http\Controllers\MetaLeadHubController;
use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\CapiDiagnosticsController;
use App\Http\Controllers\Admin\AdAlertController;
use App\Http\Controllers\Admin\AiCreativeController;
use App\Http\Controllers\Admin\AudienceController;
use App\Http\Controllers\Admin\MetaToolsController;
use App\Http\Controllers\Admin\MetaProToolsController;
use App\Http\Controllers\Admin\MetaAdvancedController;
use App\Http\Controllers\Admin\GoogleAdsController;
use App\Http\Controllers\Admin\SocialAuthController;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('admin.pos.index');
    })->name('admin.dashboard');

    // ============================================================
    // Marketing Dashboard
    // ============================================================
    Route::get('/meta-marketing', [MarketingTrackingController::class, 'metaMarketingDashboard'])->name('admin.meta-marketing.index');
    Route::post('/meta-marketing/import-page', [MarketingTrackingController::class, 'importPage'])->name('admin.meta-marketing.import-page');
    Route::post('/meta-marketing/search-page', [MarketingTrackingController::class, 'searchPage'])->name('admin.meta-marketing.search-page');
    Route::get('/meta-marketing/conversations', [MarketingTrackingController::class, 'conversations'])->name('admin.meta-marketing.conversations');
    Route::get('/meta-marketing/conversations/{id}', [MarketingTrackingController::class, 'conversationShow'])->name('admin.meta-marketing.conversation-show');
    Route::post('/meta-marketing/conversations/{id}/reply', [MarketingTrackingController::class, 'replyConversation'])->name('admin.meta-marketing.reply');
    Route::get('/meta-marketing/leads', [MarketingTrackingController::class, 'leads'])->name('admin.meta-marketing.leads');
    Route::get('/meta-marketing/audiences', [MarketingTrackingController::class, 'audiences'])->name('admin.meta-marketing.audiences');
    Route::get('/meta-marketing/webhooks', [MarketingTrackingController::class, 'webhookLogs'])->name('admin.meta-marketing.webhooks');
    Route::get('/meta-marketing/stats', [MarketingTrackingController::class, 'dashboardStats'])->name('admin.meta-marketing.stats');
    Route::get('/meta-marketing/diagnostics', [CapiDiagnosticsController::class, 'index'])->name('admin.diagnostics.index');
    Route::get('/meta-marketing/diagnostics/data', [CapiDiagnosticsController::class, 'data'])->name('admin.diagnostics.data');
    Route::delete('/meta-marketing/pages/{id}', [MarketingTrackingController::class, 'deletePage'])->name('admin.meta-marketing.delete-page');

    // Meta Hub - Unified dashboard for all Meta tools
    Route::get('/meta-hub', function () {
        return view('admin.meta-hub');
    })->name('admin.meta-hub.index');

    // ============================================================
    // Ads Management
    // ============================================================
    Route::get('/ads', [MetaAdsController::class, 'dashboard'])->name('admin.ads.dashboard');
    Route::post('/ads/accounts/connect', [MetaAdsController::class, 'connectAccount'])->name('admin.ads.connect-account');
    Route::delete('/ads/accounts/{id}', [MetaAdsController::class, 'deleteAdAccount'])->name('admin.ads.delete-account');

    // Campaigns
    Route::post('/ads/campaigns', [MetaAdsController::class, 'createCampaign'])->name('admin.ads.create-campaign');
    Route::put('/ads/campaigns/{id}', [MetaAdsController::class, 'updateCampaign'])->name('admin.ads.update-campaign');
    Route::post('/ads/campaigns/{id}/toggle', [MetaAdsController::class, 'toggleCampaign'])->name('admin.ads.toggle-campaign');
    Route::delete('/ads/campaigns/{id}', [MetaAdsController::class, 'deleteCampaign'])->name('admin.ads.delete-campaign');
    Route::post('/ads/campaigns/{id}/insights', [MetaAdsController::class, 'getCampaignInsights'])->name('admin.ads.campaign-insights');
    Route::get('/ads/campaigns/{id}/adsets', [MetaAdsController::class, 'getCampaignAdSets'])->name('admin.ads.campaign-adsets');
    Route::post('/ads/campaigns/{id}/duplicate', [MetaAdsController::class, 'duplicateCampaign'])->name('admin.ads.duplicate-campaign');

    // Ad Sets
    Route::post('/ads/adsets', [MetaAdsController::class, 'createAdSet'])->name('admin.ads.create-adset');
    Route::put('/ads/adsets/{id}', [MetaAdsController::class, 'updateAdSet'])->name('admin.ads.update-adset');
    Route::post('/ads/adsets/{id}/toggle', [MetaAdsController::class, 'toggleAdSet'])->name('admin.ads.toggle-adset');
    Route::get('/ads/adsets/{id}/ads', [MetaAdsController::class, 'getAdSetAds'])->name('admin.ads.adset-ads');
    Route::post('/ads/adsets/{id}/insights', [MetaAdsController::class, 'getAdSetInsights'])->name('admin.ads.adset-insights');

    // Creatives
    Route::post('/ads/creatives', [MetaAdsController::class, 'uploadCreative'])->name('admin.ads.upload-creative');
    Route::post('/ads/creatives/save', [MetaAdsController::class, 'saveCreative'])->name('admin.ads.save-creative');
    Route::put('/ads/creatives/{id}', [MetaAdsController::class, 'updateCreative'])->name('admin.ads.update-creative');
    Route::delete('/ads/creatives/{id}', [MetaAdsController::class, 'deleteCreative'])->name('admin.ads.delete-creative');
    Route::get('/ads/creatives/list', [MetaAdsController::class, 'getCreatives'])->name('admin.ads.list-creatives');

    // Ads
    Route::post('/ads/create', [MetaAdsController::class, 'createAd'])->name('admin.ads.create-ad');
    Route::put('/ads/{id}', [MetaAdsController::class, 'updateAd'])->name('admin.ads.update-ad');
    Route::post('/ads/{id}/toggle', [MetaAdsController::class, 'toggleAd'])->name('admin.ads.toggle-ad');
    Route::post('/ads/{id}/insights', [MetaAdsController::class, 'getAdInsights'])->name('admin.ads.ad-insights');

    // Bulk
    Route::post('/ads/insights/refresh', [MetaAdsController::class, 'refreshInsights'])->name('admin.ads.refresh-insights');
    Route::post('/ads/sync', [MetaAdsController::class, 'syncCampaigns'])->name('admin.ads.sync');

    // Google Ads Management
    Route::get('/google-ads', [GoogleAdsController::class, 'index'])->name('admin.google-ads.index');
    Route::post('/google-ads', [GoogleAdsController::class, 'store'])->name('admin.google-ads.store');
    Route::put('/google-ads/{campaignId}', [GoogleAdsController::class, 'update'])->name('admin.google-ads.update');
    Route::post('/google-ads/{campaignId}/toggle', [GoogleAdsController::class, 'toggle'])->name('admin.google-ads.toggle');
    Route::delete('/google-ads/{campaignId}', [GoogleAdsController::class, 'destroy'])->name('admin.google-ads.destroy');
    Route::get('/google-ads/{campaignId}/insights', [GoogleAdsController::class, 'insights'])->name('admin.google-ads.insights');
    Route::get('/google-ads/{campaignId}/ad-groups', [GoogleAdsController::class, 'adGroups'])->name('admin.google-ads.ad-groups');
    Route::post('/google-ads/{campaignId}/ad-groups', [GoogleAdsController::class, 'createAdGroup'])->name('admin.google-ads.create-ad-group');
    Route::get('/google-ads/ad-groups/{adGroupId}/keywords', [GoogleAdsController::class, 'keywords'])->name('admin.google-ads.keywords');
    Route::post('/google-ads/ad-groups/{adGroupId}/keywords', [GoogleAdsController::class, 'addKeyword'])->name('admin.google-ads.add-keyword');
    Route::post('/google-ads/ad-groups/{adGroupId}/responsive-ad', [GoogleAdsController::class, 'createResponsiveAd'])->name('admin.google-ads.create-responsive-ad');
    Route::get('/google-ads/test-connection', [GoogleAdsController::class, 'testConnection'])->name('admin.google-ads.test-connection');
    Route::get('/google-ads/metrics', [GoogleAdsController::class, 'getMetrics'])->name('admin.google-ads.metrics');

    // OAuth Connect for all social platforms
    Route::get('/oauth/{platform}/redirect', [\App\Http\Controllers\Admin\SocialAuthController::class, 'redirect'])
        ->name('admin.oauth.redirect');
    Route::get('/oauth/{platform}/callback', [\App\Http\Controllers\Admin\SocialAuthController::class, 'callback'])
        ->name('admin.oauth.callback');
    Route::delete('/oauth/{platform}/disconnect', [\App\Http\Controllers\Admin\SocialAuthController::class, 'disconnect'])
        ->name('admin.oauth.disconnect');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
    Route::get('/reports/sales/export', [ReportController::class, 'exportSalesExcel'])->name('admin.reports.sales.export');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('admin.reports.products');
    Route::get('/reports/products/export', [ReportController::class, 'exportProductsExcel'])->name('admin.reports.products.export');
    Route::get('/reports/users', [ReportController::class, 'users'])->name('admin.reports.users');
    Route::get('/reports/users/export', [ReportController::class, 'exportUsersExcel'])->name('admin.reports.users.export');
    Route::get('/reports/delivery', [ReportController::class, 'delivery'])->name('admin.reports.delivery');
    Route::get('/reports/delivery/export', [ReportController::class, 'exportDeliveryExcel'])->name('admin.reports.delivery.export');
    Route::get('/reports/invoice/{order}', [ReportController::class, 'invoice'])->name('admin.reports.invoice');
    Route::get('/reports/invoice/{order}/pdf/{size}', [ReportController::class, 'invoicePdf'])->name('admin.reports.invoice.pdf');

    // Hero Slides
    Route::get('/hero-slides', [HeroSlideController::class, 'index'])->name('admin.hero-slides.index');
    Route::get('/hero-slides/create', [HeroSlideController::class, 'create'])->name('admin.hero-slides.create');
    Route::post('/hero-slides', [HeroSlideController::class, 'store'])->name('admin.hero-slides.store');
    Route::get('/hero-slides/{heroSlide}/edit', [HeroSlideController::class, 'edit'])->name('admin.hero-slides.edit');
    Route::put('/hero-slides/{heroSlide}', [HeroSlideController::class, 'update'])->name('admin.hero-slides.update');
    Route::delete('/hero-slides/{heroSlide}', [HeroSlideController::class, 'destroy'])->name('admin.hero-slides.destroy');
    Route::patch('/hero-slides/{heroSlide}/toggle', [HeroSlideController::class, 'toggle'])->name('admin.hero-slides.toggle');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::get('/products/import', [ProductController::class, 'import'])->name('admin.products.import');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('admin.products.import.store');
    Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('admin.products.import.template');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Brands
    Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('admin.brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('admin.reviews.show');
    Route::patch('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::patch('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
    Route::get('/contacts/{contactMessage}', [ContactController::class, 'show'])->name('admin.contacts.show');
    Route::patch('/contacts/{contactMessage}/read', [ContactController::class, 'markRead'])->name('admin.contacts.mark-read');
    Route::delete('/contacts/{contactMessage}', [ContactController::class, 'destroy'])->name('admin.contacts.destroy');

    // B2B
    Route::get('/b2b/companies', [B2BController::class, 'companies'])->name('admin.b2b.companies');
    Route::get('/b2b/companies/{company}', [B2BController::class, 'companyShow'])->name('admin.b2b.company-show');
    Route::patch('/b2b/companies/{company}/approve', [B2BController::class, 'companyApprove'])->name('admin.b2b.company-approve');
    Route::patch('/b2b/companies/{company}/reject', [B2BController::class, 'companyReject'])->name('admin.b2b.company-reject');
    Route::get('/b2b/rfqs', [B2BController::class, 'rfqs'])->name('admin.b2b.rfqs');
    Route::get('/b2b/rfqs/{rfq}', [B2BController::class, 'rfqShow'])->name('admin.b2b.rfq-show');
    Route::patch('/b2b/rfqs/{rfq}/status', [B2BController::class, 'rfqUpdateStatus'])->name('admin.b2b.rfq-status');
    Route::get('/b2b/invoices', [B2BController::class, 'invoices'])->name('admin.b2b.invoices');
    Route::get('/b2b/invoices/{invoice}', [B2BController::class, 'invoiceShow'])->name('admin.b2b.invoice-show');

    // SEO
    Route::get('/seo', [SeoController::class, 'index'])->name('admin.seo.index');
    Route::post('/seo/auto-generate-all', [SeoController::class, 'autoGenerateAll'])->name('admin.seo.auto-all');
    Route::post('/seo/ai-generate-all', [SeoController::class, 'aiGenerateAll'])->name('admin.seo.ai-all');
    Route::get('/seo/{product}/edit', [SeoController::class, 'bulkEdit'])->name('admin.seo.edit');
    Route::post('/seo/{product}', [SeoController::class, 'bulkUpdate'])->name('admin.seo.update');
    Route::post('/seo/{product}/auto-generate', [SeoController::class, 'autoGenerate'])->name('admin.seo.auto');

    // Barcodes
    Route::get('/barcodes', [\App\Http\Controllers\Admin\BarcodeController::class, 'index'])->name('admin.barcodes.index');
    Route::get('/barcodes/count', [\App\Http\Controllers\Admin\BarcodeController::class, 'countByFilters'])->name('admin.barcodes.count');
    Route::patch('/barcodes/{product}/update', [\App\Http\Controllers\Admin\BarcodeController::class, 'updateBarcode'])->name('admin.barcodes.update');
    Route::get('/barcodes/generate-missing', [\App\Http\Controllers\Admin\BarcodeController::class, 'generateMissing'])->name('admin.barcodes.generate-missing');
    Route::post('/barcodes/print', [\App\Http\Controllers\Admin\BarcodeController::class, 'print'])->name('admin.barcodes.print');
    Route::get('/barcodes/export', [\App\Http\Controllers\Admin\BarcodeController::class, 'exportCsv'])->name('admin.barcodes.export');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('admin.notifications.unread');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('admin.deliveries.index');
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('admin.deliveries.create');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('admin.deliveries.store');
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('admin.deliveries.show');
    Route::get('/deliveries/{delivery}/edit', [DeliveryController::class, 'edit'])->name('admin.deliveries.edit');
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('admin.deliveries.update');
    Route::patch('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('admin.deliveries.update-status');
    Route::patch('/deliveries/{delivery}/driver', [DeliveryController::class, 'updateDriver'])->name('admin.deliveries.update-driver');
    Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('admin.deliveries.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::delete('/settings/delete-logo', [SettingController::class, 'deleteLogo'])->name('admin.settings.delete-logo');
    Route::post('/settings/delete-products', [SettingController::class, 'deleteAllProducts'])->name('admin.settings.delete-products');

    // Marketing
    Route::get('/marketing', [MarketingTrackingController::class, 'index'])->name('admin.marketing.index');
    Route::post('/marketing/facebook', [MarketingTrackingController::class, 'updateFacebook'])->name('admin.marketing.facebook');
    Route::post('/marketing/tiktok', [MarketingTrackingController::class, 'updateTikTok'])->name('admin.marketing.tiktok');
    Route::post('/marketing/google', [MarketingTrackingController::class, 'updateGoogle'])->name('admin.marketing.google');
    Route::post('/marketing/snapchat', [MarketingTrackingController::class, 'updateSnapchat'])->name('admin.marketing.snapchat');
    Route::post('/marketing/pinterest', [MarketingTrackingController::class, 'updatePinterest'])->name('admin.marketing.pinterest');
    Route::post('/marketing/twitter', [MarketingTrackingController::class, 'updateTwitter'])->name('admin.marketing.twitter');
    Route::post('/marketing/linkedin', [MarketingTrackingController::class, 'updateLinkedIn'])->name('admin.marketing.linkedin');
    Route::post('/marketing/shopify', [MarketingTrackingController::class, 'updateShopify'])->name('admin.marketing.shopify');
    Route::post('/marketing/woocommerce', [MarketingTrackingController::class, 'updateWooCommerce'])->name('admin.marketing.woocommerce');
    Route::post('/marketing/custom-api', [MarketingTrackingController::class, 'updateCustomApi'])->name('admin.marketing.custom-api');
    Route::post('/marketing/general', [MarketingTrackingController::class, 'updateGeneral'])->name('admin.marketing.general');
    Route::get('/marketing/test-facebook', [MarketingTrackingController::class, 'testFacebook'])->name('admin.marketing.test-facebook');
    Route::post('/marketing/oauth-credentials', [MarketingTrackingController::class, 'saveOAuthCredentials'])->name('admin.marketing.oauth-credentials');
    Route::get('/marketing/test-tiktok', [MarketingTrackingController::class, 'testTikTok'])->name('admin.marketing.test-tiktok');
    Route::get('/marketing/test-google', [MarketingTrackingController::class, 'testGoogle'])->name('admin.marketing.test-google');
    Route::get('/marketing/test-snapchat', [MarketingTrackingController::class, 'testSnapchat'])->name('admin.marketing.test-snapchat');
    Route::get('/marketing/test-pinterest', [MarketingTrackingController::class, 'testPinterest'])->name('admin.marketing.test-pinterest');
    Route::get('/marketing/test-twitter', [MarketingTrackingController::class, 'testTwitter'])->name('admin.marketing.test-twitter');
    Route::get('/marketing/test-linkedin', [MarketingTrackingController::class, 'testLinkedIn'])->name('admin.marketing.test-linkedin');
    Route::get('/marketing/test-shopify', [MarketingTrackingController::class, 'testShopify'])->name('admin.marketing.test-shopify');
    Route::get('/marketing/test-woocommerce', [MarketingTrackingController::class, 'testWooCommerce'])->name('admin.marketing.test-woocommerce');
    Route::get('/marketing/test-custom-api', [MarketingTrackingController::class, 'testCustomApi'])->name('admin.marketing.test-custom-api');
    Route::post('/marketing/send-test-event', [MarketingTrackingController::class, 'sendTestEvent'])->name('admin.marketing.send-test-event');

    // True ROAS Dashboard
    Route::get('/roas', [RoasDashboardController::class, 'index'])->name('admin.roas.index');
    Route::get('/roas/data', [RoasDashboardController::class, 'data'])->name('admin.roas.data');

    // Trigger Words (AI Compliance)
    Route::get('/trigger-words', [TriggerWordController::class, 'index'])->name('admin.trigger-words.index');
    Route::post('/trigger-words', [TriggerWordController::class, 'store'])->name('admin.trigger-words.store');
    Route::put('/trigger-words/{trigger_word}', [TriggerWordController::class, 'update'])->name('admin.trigger-words.update');
    Route::delete('/trigger-words/{trigger_word}', [TriggerWordController::class, 'destroy'])->name('admin.trigger-words.destroy');
    Route::post('/trigger-words/{trigger_word}/toggle', [TriggerWordController::class, 'toggle'])->name('admin.trigger-words.toggle');

    // AI Compliance Dashboard
    Route::get('/ai-compliance', [AiComplianceController::class, 'index'])->name('admin.ai-compliance.index');
    Route::get('/ai-compliance/refresh-health', [AiComplianceController::class, 'refreshHealth'])->name('admin.ai-compliance.refresh-health');
    Route::post('/ai-compliance/test-sanitization', [AiComplianceController::class, 'testSanitization'])->name('admin.ai-compliance.test');

    // Predictive Dashboard
    Route::get('/predictive', [PredictiveController::class, 'index'])->name('admin.predictive.index');
    Route::get('/predictive/data', [PredictiveController::class, 'data'])->name('admin.predictive.data');

    // Reviewer IPs
    Route::get('/reviewer-ips', [ReviewerIpController::class, 'index'])->name('admin.reviewer-ips.index');
    Route::post('/reviewer-ips', [ReviewerIpController::class, 'store'])->name('admin.reviewer-ips.store');
    Route::delete('/reviewer-ips/{reviewer_ip}', [ReviewerIpController::class, 'destroy'])->name('admin.reviewer-ips.destroy');
    Route::post('/reviewer-ips/{reviewer_ip}/toggle', [ReviewerIpController::class, 'toggle'])->name('admin.reviewer-ips.toggle');

    // Affiliate Management
    Route::get('/affiliates', [AdminAffiliateController::class, 'index'])->name('admin.affiliates.index');
    Route::get('/affiliates/commissions/list', [AdminAffiliateController::class, 'commissions'])->name('admin.affiliates.commissions');
    Route::get('/affiliates/payouts/list', [AdminAffiliateController::class, 'payouts'])->name('admin.affiliates.payouts');
    Route::get('/affiliates/{affiliate}', [AdminAffiliateController::class, 'show'])->name('admin.affiliates.show');
    Route::patch('/affiliates/{affiliate}/status', [AdminAffiliateController::class, 'updateStatus'])->name('admin.affiliates.status');
    Route::patch('/affiliates/{affiliate}/commission', [AdminAffiliateController::class, 'updateCommission'])->name('admin.affiliates.commission');
    Route::patch('/affiliates/{affiliate}/tier', [AdminAffiliateController::class, 'updateTier'])->name('admin.affiliates.tier');
    Route::patch('/affiliates/{affiliate}/notes', [AdminAffiliateController::class, 'notes'])->name('admin.affiliates.notes');
    Route::patch('/affiliates/commissions/{commission}/approve', [AdminAffiliateController::class, 'approveCommission'])->name('admin.affiliates.commissions.approve');
    Route::patch('/affiliates/commissions/{commission}/reject', [AdminAffiliateController::class, 'rejectCommission'])->name('admin.affiliates.commissions.reject');
    Route::patch('/affiliates/payouts/{payout}/process', [AdminAffiliateController::class, 'processPayout'])->name('admin.affiliates.payouts.process');

    // Blog Management
    Route::get('/blog', [AdminBlogController::class, 'index'])->name('admin.blog.index');
    Route::get('/blog/create', [AdminBlogController::class, 'create'])->name('admin.blog.create');
    Route::post('/blog', [AdminBlogController::class, 'store'])->name('admin.blog.store');
    Route::get('/blog/{blog}/edit', [AdminBlogController::class, 'edit'])->name('admin.blog.edit');
    Route::put('/blog/{blog}', [AdminBlogController::class, 'update'])->name('admin.blog.update');
    Route::delete('/blog/{blog}', [AdminBlogController::class, 'destroy'])->name('admin.blog.destroy');
    Route::patch('/blog/{blog}/toggle', [AdminBlogController::class, 'toggle'])->name('admin.blog.toggle');
    Route::patch('/blog/{id}/restore', [AdminBlogController::class, 'restore'])->name('admin.blog.restore');
    Route::delete('/blog/trash/empty', [AdminBlogController::class, 'emptyTrash'])->name('admin.blog.empty-trash');
    Route::post('/blog/upload-inline-image', [AdminBlogController::class, 'uploadInlineImage'])->name('admin.blog.upload-inline-image');

    // Facebook Leads Hub
    Route::get('/leads-hub', [MetaLeadHubController::class, 'index'])->name('admin.leads-hub.index');
    Route::get('/leads-hub/filter', [MetaLeadHubController::class, 'filter'])->name('admin.leads-hub.filter');
    Route::get('/leads-hub/stats', [MetaLeadHubController::class, 'stats'])->name('admin.leads-hub.stats');
    Route::post('/leads-hub/sync', [MetaLeadHubController::class, 'sync'])->name('admin.leads-hub.sync');
    Route::post('/leads-hub/sync-facebook', [MetaLeadHubController::class, 'syncFromFacebook'])->name('admin.leads-hub.sync-facebook');
    Route::post('/leads-hub/bulk-message', [MetaLeadHubController::class, 'bulkMessage'])->name('admin.leads-hub.bulk-message');
    Route::get('/leads-hub/bulk-campaigns', [MetaLeadHubController::class, 'bulkCampaigns'])->name('admin.leads-hub.bulk-campaigns');
    Route::get('/leads-hub/bulk-campaigns/{campaign}', [MetaLeadHubController::class, 'bulkCampaignShow'])->name('admin.leads-hub.bulk-campaigns.show');
    Route::get('/leads-hub/export', [MetaLeadHubController::class, 'exportExcel'])->name('admin.leads-hub.export');
    Route::get('/leads-hub/export-selected', [MetaLeadHubController::class, 'exportSelected'])->name('admin.leads-hub.export-selected');
    Route::get('/leads-hub/{lead}', [MetaLeadHubController::class, 'show'])->name('admin.leads-hub.show');
    Route::post('/leads-hub/{lead}/score', [MetaLeadHubController::class, 'updateScore'])->name('admin.leads-hub.score');
    Route::post('/leads-hub/{lead}/stage', [MetaLeadHubController::class, 'updateStage'])->name('admin.leads-hub.stage');
    Route::post('/leads-hub/{lead}/tag', [MetaLeadHubController::class, 'addTag'])->name('admin.leads-hub.tag');
    Route::delete('/leads-hub/{lead}/tag', [MetaLeadHubController::class, 'removeTag'])->name('admin.leads-hub.remove-tag');

    // POS System
    Route::get('/pos', [PosController::class, 'index'])->name('admin.pos.index');
    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])->name('admin.pos.products.search');
    Route::post('/pos/sale', [PosController::class, 'store'])->name('admin.pos.sale.store');
    Route::get('/pos/recent-sales', [PosController::class, 'recentSales'])->name('admin.pos.recent-sales');
    Route::post('/pos/suspend', [PosController::class, 'suspendCart'])->name('admin.pos.suspend');
    Route::get('/pos/suspended', [PosController::class, 'suspendedCarts'])->name('admin.pos.suspended');
    Route::post('/pos/suspended/{id}/restore', [PosController::class, 'restoreCart'])->name('admin.pos.suspended.restore');
    Route::delete('/pos/suspended/{id}', [PosController::class, 'deleteSuspendedCart'])->name('admin.pos.suspended.delete');
    Route::get('/pos/sales/{posSaleId}', [PosController::class, 'getSale'])->name('admin.pos.getSale')->where('posSaleId', '[A-Za-z0-9\-]+');
    Route::post('/pos/sales/{posSaleId}/edit', [PosController::class, 'editSale'])->name('admin.pos.editSale')->where('posSaleId', '[A-Za-z0-9\-]+');
    Route::delete('/pos/sales/{posSaleId}', [PosController::class, 'deleteSale'])->name('admin.pos.deleteSale')->where('posSaleId', '[A-Za-z0-9\-]+');
    Route::get('/pos/receipt/{posSaleId}', [PosController::class, 'printReceipt'])->name('admin.pos.receipt')->where('posSaleId', '[A-Za-z0-9\-]+');
    Route::get('/pos/customers/search', [PosController::class, 'searchCustomers'])->name('admin.pos.searchCustomers');
    Route::post('/pos/customers/create', [PosController::class, 'createCustomer'])->name('admin.pos.createCustomer');
    Route::get('/pos/customers/{id}/history', [PosController::class, 'customerHistory'])->name('admin.pos.customerHistory');
    Route::post('/pos/products/quick-create', [PosController::class, 'quickCreateProduct'])->name('admin.pos.quickProduct');
    Route::get('/pos/favorites', [PosController::class, 'getFavorites'])->name('admin.pos.getFavorites');
    Route::post('/pos/favorites/toggle', [PosController::class, 'toggleFavorite'])->name('admin.pos.toggleFavorite');
    Route::post('/pos/refund', [PosController::class, 'processRefund'])->name('admin.pos.refund');

    // SkinAnalyzer Section
    Route::get('/skinanalyzer/stats', [DashboardController::class, 'skinAnalyzerStats'])->name('admin.skinanalyzer.stats');
    Route::get('/skinanalyzer/scans/pending', [DashboardController::class, 'pendingSkinScans'])->name('admin.skinanalyzer.scans.pending');
    Route::get('/skinanalyzer/scans/all', [DashboardController::class, 'allSkinScans'])->name('admin.skinanalyzer.scans.all');
    Route::get('/skinanalyzer/scans/{id}', [DashboardController::class, 'skinScanDetail'])->name('admin.skinanalyzer.scans.detail');

    Route::post('/skinanalyzer/scans/{id}/approve', [\App\Http\Controllers\Admin\ScanApprovalController::class, 'approve'])->name('admin.skinanalyzer.scans.approve');
    Route::post('/skinanalyzer/scans/{id}/reject', [\App\Http\Controllers\Admin\ScanApprovalController::class, 'reject'])->name('admin.skinanalyzer.scans.reject');
    Route::post('/skinanalyzer/scans/{id}/generate-pin', [\App\Http\Controllers\Admin\ScanApprovalController::class, 'generatePin'])->name('admin.skinanalyzer.scans.generate-pin');
    Route::post('/skinanalyzer/scans/batch-approve', [\App\Http\Controllers\Admin\ScanApprovalController::class, 'batchApprove'])->name('admin.skinanalyzer.scans.batch-approve');
    Route::post('/skinanalyzer/scans/{id}/broadcast', [\App\Http\Controllers\Admin\ScanApprovalController::class, 'broadcastResult'])->name('admin.skinanalyzer.scans.broadcast');

    Route::get('/skinanalyzer/ai-providers', [\App\Http\Controllers\Admin\AIProviderController::class, 'index'])->name('admin.skinanalyzer.providers.index');
    Route::get('/skinanalyzer/ai-providers/quota', [\App\Http\Controllers\Admin\AIProviderController::class, 'quotaStatus'])->name('admin.skinanalyzer.providers.quota');
    Route::get('/skinanalyzer/ai-providers/{id}', [\App\Http\Controllers\Admin\AIProviderController::class, 'show'])->name('admin.skinanalyzer.providers.show');
    Route::put('/skinanalyzer/ai-providers/{id}', [\App\Http\Controllers\Admin\AIProviderController::class, 'update'])->name('admin.skinanalyzer.providers.update');
    Route::post('/skinanalyzer/ai-providers/{id}/activate', [\App\Http\Controllers\Admin\AIProviderController::class, 'activate'])->name('admin.skinanalyzer.providers.activate');
    Route::post('/skinanalyzer/ai-providers/{id}/deactivate', [\App\Http\Controllers\Admin\AIProviderController::class, 'deactivate'])->name('admin.skinanalyzer.providers.deactivate');
    Route::post('/skinanalyzer/ai-providers/{id}/test', [\App\Http\Controllers\Admin\AIProviderController::class, 'testConnection'])->name('admin.skinanalyzer.providers.test');

    Route::get('/skinanalyzer/prompts', [\App\Http\Controllers\Admin\PromptController::class, 'index'])->name('admin.skinanalyzer.prompts.index');
    Route::get('/skinanalyzer/prompts/{id}', [\App\Http\Controllers\Admin\PromptController::class, 'show'])->name('admin.skinanalyzer.prompts.show');
    Route::post('/skinanalyzer/prompts', [\App\Http\Controllers\Admin\PromptController::class, 'store'])->name('admin.skinanalyzer.prompts.store');
    Route::put('/skinanalyzer/prompts/{id}', [\App\Http\Controllers\Admin\PromptController::class, 'update'])->name('admin.skinanalyzer.prompts.update');
    Route::get('/skinanalyzer/prompts/variables', [\App\Http\Controllers\Admin\PromptController::class, 'variables'])->name('admin.skinanalyzer.prompts.variables');

    Route::get('/skinanalyzer/white-label', [\App\Http\Controllers\Admin\WhiteLabelController::class, 'show'])->name('admin.skinanalyzer.whitelabel.show');
    Route::put('/skinanalyzer/white-label', [\App\Http\Controllers\Admin\WhiteLabelController::class, 'update'])->name('admin.skinanalyzer.whitelabel.update');
    Route::post('/skinanalyzer/white-label/logo', [\App\Http\Controllers\Admin\WhiteLabelController::class, 'uploadLogo'])->name('admin.skinanalyzer.whitelabel.logo');
    Route::get('/skinanalyzer/white-label/preview', [\App\Http\Controllers\Admin\WhiteLabelController::class, 'preview'])->name('admin.skinanalyzer.whitelabel.preview');

    // ============================================================
    // Ad Alerts
    // ============================================================
    Route::get('/ad-alerts', [AdAlertController::class, 'index'])->name('admin.ad-alerts.index');
    Route::get('/ad-alerts/pause-log', [AdAlertController::class, 'pauseLog'])->name('admin.ad-alerts.pause-log');
    Route::post('/ad-alerts/{alert}/acknowledge', [AdAlertController::class, 'acknowledge'])->name('admin.ad-alerts.acknowledge');
    Route::post('/ad-alerts/{alert}/resolve', [AdAlertController::class, 'resolve'])->name('admin.ad-alerts.resolve');
    Route::delete('/ad-alerts/{alert}', [AdAlertController::class, 'destroy'])->name('admin.ad-alerts.destroy');
    Route::get('/ad-alerts/health-summary', [AdAlertController::class, 'healthSummary'])->name('admin.ad-alerts.health-summary');
    Route::get('/ad-alerts/active-count', [AdAlertController::class, 'activeAlertsCount'])->name('admin.ad-alerts.active-count');

    // AI Creative Copilot
    Route::prefix('ai-creative')->group(function () {
        Route::get('/', [AiCreativeController::class, 'index'])->name('admin.ai-creative.index');
        Route::get('/generate', [AiCreativeController::class, 'generateForm'])->name('admin.ai-creative.generate-form');
        Route::post('/generate', [AiCreativeController::class, 'generate'])->name('admin.ai-creative.generate');
        Route::post('/store', [AiCreativeController::class, 'store'])->name('admin.ai-creative.store');
        Route::delete('/{id}', [AiCreativeController::class, 'destroy'])->name('admin.ai-creative.destroy');
    });

    // Audience Builder
    Route::prefix('audiences')->group(function () {
        Route::get('/', [AudienceController::class, 'index'])->name('admin.audiences.index');
        Route::get('/create', [AudienceController::class, 'create'])->name('admin.audiences.create');
        Route::post('/', [AudienceController::class, 'store'])->name('admin.audiences.store');
        Route::get('/{audience}', [AudienceController::class, 'show'])->name('admin.audiences.show');
        Route::post('/{audience}/sync', [AudienceController::class, 'sync'])->name('admin.audiences.sync');
        Route::post('/{audience}/push', [AudienceController::class, 'pushToPlatform'])->name('admin.audiences.push');
        Route::delete('/{audience}', [AudienceController::class, 'destroy'])->name('admin.audiences.destroy');
        Route::post('/lookalike', [AudienceController::class, 'createLookalike'])->name('admin.audiences.lookalike');
        Route::post('/overlap', [AudienceController::class, 'overlapAnalysis'])->name('admin.audiences.overlap');
    });

    // ============================================================
    // Meta Tools
    // ============================================================

    // WhatsApp
    Route::get('/meta-tools/whatsapp', [MetaToolsController::class, 'whatsappDashboard'])->name('admin.meta-tools.whatsapp');
    Route::post('/meta-tools/whatsapp/send', [MetaToolsController::class, 'whatsappSend'])->name('admin.meta-tools.whatsapp-send');
    Route::post('/meta-tools/whatsapp/bulk', [MetaToolsController::class, 'whatsappBulkSend'])->name('admin.meta-tools.whatsapp-bulk');
    Route::get('/meta-tools/whatsapp/test', [MetaToolsController::class, 'whatsappTest'])->name('admin.meta-tools.whatsapp-test');

    // Conversations
    Route::get('/meta-tools/conversations', [MetaToolsController::class, 'conversationsIndex'])->name('admin.meta-tools.conversations');
    Route::get('/meta-tools/conversations/{id}/messages', [MetaToolsController::class, 'conversationsMessages'])->name('admin.meta-tools.conversation-messages');
    Route::post('/meta-tools/conversations/reply', [MetaToolsController::class, 'conversationsReply'])->name('admin.meta-tools.conversation-reply');
    Route::get('/meta-tools/conversations/unread', [MetaToolsController::class, 'conversationsUnread'])->name('admin.meta-tools.conversation-unread');

    // Pixel Helper
    Route::get('/meta-tools/pixel-helper', [MetaToolsController::class, 'pixelHelperIndex'])->name('admin.meta-tools.pixel-helper');
    Route::get('/meta-tools/pixel-helper/verify', [MetaToolsController::class, 'pixelHelperVerify'])->name('admin.meta-tools.pixel-verify');
    Route::get('/meta-tools/pixel-helper/health', [MetaToolsController::class, 'pixelHelperHealth'])->name('admin.meta-tools.pixel-health');

    // A/B Testing
    Route::get('/meta-tools/ab-tests', [MetaToolsController::class, 'abTestsIndex'])->name('admin.ab-tests.index');
    Route::post('/meta-tools/ab-tests', [MetaToolsController::class, 'abTestsCreate'])->name('admin.ab-tests.create');
    Route::get('/meta-tools/ab-tests/{id}/analyze', [MetaToolsController::class, 'abTestsAnalyze'])->name('admin.ab-tests.analyze');
    Route::post('/meta-tools/ab-tests/{id}/winner', [MetaToolsController::class, 'abTestsDeclareWinner'])->name('admin.ab-tests.winner');

    // Instagram
    Route::get('/meta-tools/instagram', [MetaToolsController::class, 'instagramDashboard'])->name('admin.meta-tools.instagram');
    Route::get('/meta-tools/instagram/insights', [MetaToolsController::class, 'instagramInsights'])->name('admin.meta-tools.instagram-insights');
    Route::get('/meta-tools/instagram/top-posts', [MetaToolsController::class, 'instagramTopPosts'])->name('admin.meta-tools.instagram-top-posts');

    // Audience Upload
    Route::get('/meta-tools/audience-upload', [MetaToolsController::class, 'audienceUploadIndex'])->name('admin.meta-tools.audience-upload');
    Route::post('/meta-tools/audience-upload/csv', [MetaToolsController::class, 'audienceUploadCsv'])->name('admin.meta-tools.audience-upload-csv');
    Route::post('/meta-tools/audience-upload/phones', [MetaToolsController::class, 'audienceUploadPhones'])->name('admin.meta-tools.audience-upload-phones');
    Route::post('/meta-tools/audience-upload/emails', [MetaToolsController::class, 'audienceUploadEmails'])->name('admin.meta-tools.audience-upload-emails');
    Route::get('/meta-tools/audience-upload/template', [MetaToolsController::class, 'audienceTemplate'])->name('admin.meta-tools.audience-template');

    // Enhanced Matching
    Route::post('/meta-tools/enhanced-matching/test', [MetaToolsController::class, 'enhancedMatchingTest'])->name('admin.meta-tools.enhanced-matching');

    // ============================================================
    // Meta Pro Tools - Advanced Advertising Tools
    // ============================================================

    Route::prefix('meta-pro-tools')->name('admin.meta-pro-tools.')->group(function () {
        Route::get('/', [MetaProToolsController::class, 'index'])->name('index');

        // Ad Preview
        Route::get('/ad-preview/{creative}', [MetaProToolsController::class, 'adPreview'])->name('ad-preview');
        Route::get('/ad-preview/{creative}/all', [MetaProToolsController::class, 'adPreviewAll'])->name('ad-preview-all');
        Route::post('/validate-ad', [MetaProToolsController::class, 'validateAd'])->name('validate-ad');

        // Copy Generator
        Route::get('/copy-generator', [MetaProToolsController::class, 'copyGeneratorIndex'])->name('copy-generator');
        Route::post('/copy-generator/generate', [MetaProToolsController::class, 'copyGeneratorGenerate'])->name('copy-generator.generate');

        // Budget Optimizer
        Route::get('/budget-optimizer', [MetaProToolsController::class, 'budgetOptimizerIndex'])->name('budget-optimizer');
        Route::get('/budget-optimizer/analyze/{account}', [MetaProToolsController::class, 'budgetOptimizerAnalyze'])->name('budget-optimizer.analyze');

        // Performance Forecast
        Route::get('/performance-forecast', [MetaProToolsController::class, 'performanceForecastIndex'])->name('performance-forecast');
        Route::get('/performance-forecast/campaign/{campaignId}', [MetaProToolsController::class, 'performanceForecastCampaign'])->name('performance-forecast.campaign');
        Route::get('/performance-forecast/account/{accountId}', [MetaProToolsController::class, 'performanceForecastAccount'])->name('performance-forecast.account');

        // Placement Recommendations
        Route::get('/placement-recommendations', [MetaProToolsController::class, 'placementRecommendations'])->name('placement-recommendations');
        Route::get('/placement-recommendations/{objective}', [MetaProToolsController::class, 'placementForObjective'])->name('placement-recommendations.objective');

        // Schedule Optimizer
        Route::get('/schedule-optimizer', [MetaProToolsController::class, 'scheduleOptimizer'])->name('schedule-optimizer');
        Route::get('/schedule-optimizer/{campaignId}', [MetaProToolsController::class, 'scheduleForCampaign'])->name('schedule-optimizer.campaign');

        // Ad Library
        Route::get('/ad-library', [MetaProToolsController::class, 'adLibraryIndex'])->name('ad-library');
        Route::post('/ad-library/search', [MetaProToolsController::class, 'adLibrarySearch'])->name('ad-library.search');
        Route::post('/ad-library/by-page', [MetaProToolsController::class, 'adLibraryByPage'])->name('ad-library.by-page');
        Route::post('/ad-library/industry', [MetaProToolsController::class, 'adLibraryIndustry'])->name('ad-library.industry');

        // Pre-Flight Compliance
        Route::post('/pre-flight-check', [MetaProToolsController::class, 'preFlightCheck'])->name('pre-flight-check');
        Route::get('/compliance-rules', [MetaProToolsController::class, 'complianceRules'])->name('compliance-rules');
    });

    // ============================================================
    // Meta Advanced Features
    // ============================================================

    Route::prefix('meta-advanced')->name('admin.meta-advanced.')->group(function () {
        Route::get('/', [MetaAdvancedController::class, 'dashboard'])->name('dashboard');

        // Analytics
        Route::get('/analytics', [MetaAdvancedController::class, 'analyticsIndex'])->name('analytics');

        // Automation
        Route::get('/automation', [MetaAdvancedController::class, 'automationIndex'])->name('automation');
        Route::post('/automation/rules', [MetaAdvancedController::class, 'createAutomationRule'])->name('automation.rules.store');
        Route::put('/automation/rules/{id}', [MetaAdvancedController::class, 'updateAutomationRule'])->name('automation.rules.update');
        Route::delete('/automation/rules/{id}', [MetaAdvancedController::class, 'deleteAutomationRule'])->name('automation.rules.destroy');
        Route::post('/automation/execute', [MetaAdvancedController::class, 'executeAutomationRules'])->name('automation.execute');
        Route::post('/automation/schedule', [MetaAdvancedController::class, 'scheduleCampaignAction'])->name('automation.schedule');
        Route::post('/automation/scheduled/{id}/cancel', [MetaAdvancedController::class, 'cancelScheduledAction'])->name('automation.schedule.cancel');

        // Creative Optimization
        Route::get('/creative', [MetaAdvancedController::class, 'creativeIndex'])->name('creative');
        Route::get('/creative/{id}/analyze', [MetaAdvancedController::class, 'analyzeCreativeFatigue'])->name('creative.analyze');
        Route::get('/creative/{id}/suggestions', [MetaAdvancedController::class, 'getCreativeSuggestions'])->name('creative.suggestions');
        Route::post('/creative/compare', [MetaAdvancedController::class, 'compareCreatives'])->name('creative.compare');

        // Compliance
        Route::get('/compliance', [MetaAdvancedController::class, 'complianceIndex'])->name('compliance');
        Route::post('/compliance/issues/{id}/resolve', [MetaAdvancedController::class, 'resolveComplianceIssue'])->name('compliance.resolve');
        Route::get('/compliance/health/{accountId}', [MetaAdvancedController::class, 'checkAccountHealth'])->name('compliance.health');
        Route::post('/compliance/spending-limits', [MetaAdvancedController::class, 'createSpendingLimit'])->name('compliance.limits.store');
        Route::post('/compliance/check-limits', [MetaAdvancedController::class, 'checkSpendingLimits'])->name('compliance.limits.check');

        // Leads
        Route::get('/leads', [MetaAdvancedController::class, 'leadsIndex'])->name('leads');
        Route::post('/leads/{leadId}/conversion', [MetaAdvancedController::class, 'trackLeadConversion'])->name('leads.conversion');
        Route::post('/leads/auto-score', [MetaAdvancedController::class, 'autoScoreLeads'])->name('leads.auto-score');

        // Targeting
        Route::get('/targeting', [MetaAdvancedController::class, 'targetingIndex'])->name('targeting');
        Route::post('/targeting/lookalike', [MetaAdvancedController::class, 'createLookalikeAudience'])->name('targeting.lookalike');
        Route::post('/targeting/retargeting', [MetaAdvancedController::class, 'createRetargetingAudience'])->name('targeting.retargeting');
        Route::get('/targeting/suggestions/{campaignId}', [MetaAdvancedController::class, 'getAudienceSuggestions'])->name('targeting.suggestions');

        // Reports
        Route::get('/reports', [MetaAdvancedController::class, 'reportsIndex'])->name('reports');
        Route::post('/reports', [MetaAdvancedController::class, 'createAutomatedReport'])->name('reports.store');
        Route::post('/reports/{id}/generate', [MetaAdvancedController::class, 'generateReport'])->name('reports.generate');
        Route::delete('/reports/{id}', [MetaAdvancedController::class, 'deleteAutomatedReport'])->name('reports.destroy');
    });
});
