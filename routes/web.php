<?php

use App\Http\Controllers\Ajax\CartController as CartAjaxController;
use App\Http\Controllers\Ajax\NewsletterController;
use App\Http\Controllers\Ajax\SuggestController;
use App\Http\Controllers\Ajax\WishlistController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\DisputeController;
use App\Http\Controllers\Storefront\DocumentController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\PaymentController;
use App\Http\Controllers\Storefront\PlaceholderController;
use App\Http\Controllers\Storefront\PrivateDocumentController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\RfqController;
use App\Http\Controllers\Storefront\SitemapController;
use App\Http\Controllers\Storefront\TrackingController;
use App\Http\Controllers\Storefront\VendorDirectoryController;
use App\Http\Controllers\Vendor\VendorOnboardingController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

/** Register a named route that renders the "under construction" screen. */
$placeholder = function (string $uri, string $name, string $heading, ?string $note = null): void {
    Route::get($uri, fn () => app(PlaceholderController::class)($heading, $note))->name($name);
};

// Catalogue
Route::get('/verticals/{vertical}', [CatalogController::class, 'vertical'])->name('verticals.show');
Route::get('/c/{category}', [CatalogController::class, 'category'])->name('categories.show');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/deals', [CatalogController::class, 'deals'])->name('deals');
Route::get('/new-arrivals', [CatalogController::class, 'newArrivals'])->name('new-arrivals');
Route::get('/p/{product}', [ProductController::class, 'show'])->name('products.show');

// Vendors
Route::get('/vendors', [VendorDirectoryController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{vendor}', [VendorDirectoryController::class, 'show'])->name('vendors.show');

// Cart is available to guests; checkout is not (B2B buyers must be verified).
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// Private documents — authorised and streamed, never served from public disk.
Route::get('/documents/product/{document}', [PrivateDocumentController::class, 'product'])->name('documents.product');
Route::get('/documents/vendor/{document}', [PrivateDocumentController::class, 'vendor'])->name('documents.vendor');

// Tracking
Route::get('/track-order', TrackingController::class)->name('track-order');

// SEO & Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Content — Phase 8
$placeholder('/help', 'help', 'Help center');
$placeholder('/contact', 'contact', 'Contact us');
$placeholder('/blog', 'blog.index', 'Insights & export trends');
$placeholder('/pages/{page}', 'pages.show', 'Information');

/*
|--------------------------------------------------------------------------
| Webhooks (Public with Signature Verification)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');

/*
|--------------------------------------------------------------------------
| Vendor onboarding
|--------------------------------------------------------------------------
*/

Route::view('/become-a-vendor', 'vendor.become-a-vendor')->name('become-vendor');

Route::middleware('auth')->prefix('vendor/onboarding')->name('vendor.onboarding.')->group(function (): void {
    Route::get('/', [VendorOnboardingController::class, 'create'])->name('create');
    Route::post('/', [VendorOnboardingController::class, 'store'])->name('store');
    Route::get('/status', [VendorOnboardingController::class, 'status'])->name('status');
});

/*
|--------------------------------------------------------------------------
| Checkout, payment, quotes & account
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

    Route::get('/checkout/{order}/pay', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/checkout/{order}/process-payment', [PaymentController::class, 'process'])->name('payment.process');
    Route::post('/checkout/{order}/complete', [PaymentController::class, 'complete'])->name('payment.complete');
    Route::get('/checkout/{order}/proforma', [PaymentController::class, 'proforma'])->name('payment.proforma');

    Route::get('/rfq/create', [RfqController::class, 'create'])->name('rfq.create');
    Route::post('/rfq', [RfqController::class, 'store'])->middleware('throttle:20,1')->name('rfq.store');

    Route::prefix('account')->name('account.')->group(function (): void {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AccountController::class, 'order'])->name('orders.show');
        Route::get('/orders/{order}/documents/{type}', [DocumentController::class, 'show'])->name('orders.documents');
        Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
        Route::get('/rfqs', [AccountController::class, 'rfqs'])->name('rfqs');
        Route::get('/rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
        Route::post('/rfqs/{rfq}/quotes/{quote}/accept', [RfqController::class, 'acceptQuote'])->name('quotes.accept');

        Route::post('/orders/{order}/sub-orders/{subOrder}/disputes', [DisputeController::class, 'store'])->name('disputes.store');
        Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
        Route::post('/disputes/{dispute}/reply', [DisputeController::class, 'reply'])->name('disputes.reply');
    });
});

/*
|--------------------------------------------------------------------------
| JSON endpoints consumed by Alpine
|--------------------------------------------------------------------------
*/

Route::prefix('x')->group(function (): void {
    Route::get('cart', [CartAjaxController::class, 'show'])->name('cart.show');
    Route::post('cart/items', [CartAjaxController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{item}', [CartAjaxController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{item}', [CartAjaxController::class, 'destroy'])->name('cart.items.destroy');

    Route::post('wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('search/suggest', SuggestController::class)->middleware('throttle:60,1')->name('search.suggest');

    Route::post('newsletter', NewsletterController::class)
        ->middleware('throttle:10,1')
        ->name('newsletter.store');
});

require __DIR__.'/auth.php';
