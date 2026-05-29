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

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
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
    Route::delete('/meta-marketing/pages/{id}', [MarketingTrackingController::class, 'deletePage'])->name('admin.meta-marketing.delete-page');

    // Ads Management
    Route::get('/ads', [MetaAdsController::class, 'dashboard'])->name('admin.ads.dashboard');
    Route::post('/ads/accounts/connect', [MetaAdsController::class, 'connectAccount'])->name('admin.ads.connect-account');
    Route::delete('/ads/accounts/{id}', [MetaAdsController::class, 'deleteAdAccount'])->name('admin.ads.delete-account');
    Route::post('/ads/campaigns', [MetaAdsController::class, 'createCampaign'])->name('admin.ads.create-campaign');
    Route::post('/ads/campaigns/{id}/toggle', [MetaAdsController::class, 'toggleCampaign'])->name('admin.ads.toggle-campaign');
    Route::delete('/ads/campaigns/{id}', [MetaAdsController::class, 'deleteCampaign'])->name('admin.ads.delete-campaign');
    Route::post('/ads/campaigns/{id}/insights', [MetaAdsController::class, 'getInsights'])->name('admin.ads.insights');
    Route::post('/ads/adsets', [MetaAdsController::class, 'createAdSet'])->name('admin.ads.create-adset');
    Route::post('/ads/adsets/{id}/toggle', [MetaAdsController::class, 'toggleAdSet'])->name('admin.ads.toggle-adset');
    Route::post('/ads/creatives', [MetaAdsController::class, 'uploadCreative'])->name('admin.ads.upload-creative');
    Route::post('/ads/creatives/save', [MetaAdsController::class, 'saveCreative'])->name('admin.ads.save-creative');
    Route::post('/ads/create', [MetaAdsController::class, 'createAd'])->name('admin.ads.create-ad');
    Route::post('/ads/{id}/toggle', [MetaAdsController::class, 'toggleAd'])->name('admin.ads.toggle-ad');
    Route::post('/ads/insights/refresh', [MetaAdsController::class, 'refreshInsights'])->name('admin.ads.refresh-insights');
    Route::post('/ads/sync', [MetaAdsController::class, 'syncCampaigns'])->name('admin.ads.sync');

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
    Route::get('/seo/{product}/edit', [SeoController::class, 'bulkEdit'])->name('admin.seo.edit');
    Route::post('/seo/{product}', [SeoController::class, 'bulkUpdate'])->name('admin.seo.update');
    Route::post('/seo/{product}/auto-generate', [SeoController::class, 'autoGenerate'])->name('admin.seo.auto');

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
    Route::post('/marketing/oauth-credentials', [MarketingTrackingController::class, 'saveOAuthCredentials'])->name('admin.marketing.oauth-credentials');    Route::get('/marketing/test-tiktok', [MarketingTrackingController::class, 'testTikTok'])->name('admin.marketing.test-tiktok');
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
});
