<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\VendorObserver;
use App\Services\CartService;
use App\Support\Navigation;
use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request, so a page rendering a dozen sections reads
        // the settings cache once.
        $this->app->singleton(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict($this->app->isLocal());
        Model::automaticallyEagerLoadRelationships();

        if ($this->app->isProduction()) {
            // Behind a load balancer the app only sees http; force every
            // generated URL (payment returns, webhooks, emails) onto https.
            URL::forceScheme('https');

            // Destructive artisan commands must be confirmed on production.
            DB::prohibitDestructiveCommands();
        }

        $this->shareNavigation();
        $this->shareStorefrontState();

        Product::observe(ProductObserver::class);
        Vendor::observe(VendorObserver::class);
        Category::observe(CategoryObserver::class);
    }

    /**
     * Bootstraps the Alpine cart/wishlist stores so badges are correct on the
     * first paint instead of flashing in after an XHR.
     */
    private function shareStorefrontState(): void
    {
        View::composer('components.layouts.storefront', function ($view): void {
            $cart = app(CartService::class);

            $view->with('storefrontState', [
                'cart' => Arr::only($cart->payload(), ['count', 'total']),
                'wishlist' => request()->user()
                    ? Wishlist::where('user_id', request()->user()->id)->pluck('product_id')->all()
                    : [],
            ]);
        });
    }

    /**
     * The header/mega-menu and search dropdown all need the vertical tree.
     * Cached so it costs one query per hour instead of one per request.
     */
    private function shareNavigation(): void
    {
        View::composer([
            'components.nav.category-nav',
            'components.nav.mobile-menu',
            'components.search.bar',
        ], fn ($view) => $view->with('navVerticals', Navigation::verticals()));
    }
}
