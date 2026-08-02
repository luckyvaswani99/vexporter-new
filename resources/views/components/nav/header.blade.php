<header class="sticky top-0 z-50 glass border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <x-brand.logo :href="route('home')" size="md" />

            <div class="hidden lg:flex flex-1 max-w-2xl mx-10">
                <x-search.bar />
            </div>

            <div class="flex items-center gap-2 sm:gap-5">
                @if (setting('header.show_wishlist', true))
                    <a href="{{ route('account.wishlist') }}" class="hidden md:flex flex-col items-center text-gray-600 hover:text-brand-red transition text-xs gap-0.5">
                        <i class="far fa-heart text-lg"></i>
                        <span>Wishlist</span>
                    </a>
                @endif

                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="hidden md:flex flex-col items-center text-gray-600 hover:text-brand-red transition text-xs gap-0.5">
                    <i class="far fa-user text-lg"></i>
                    <span>{{ auth()->check() ? 'Account' : 'Sign in' }}</span>
                </a>

                <a href="{{ route('cart.index') }}" class="flex flex-col items-center text-gray-600 hover:text-brand-red transition text-xs gap-0.5">
                    <span class="relative">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span
                            x-data
                            x-show="$store.cart.count > 0"
                            x-text="$store.cart.count"
                            x-cloak
                            class="absolute -top-2 -right-2 bg-brand-red text-white text-[10px] font-bold min-w-4 h-4 px-1 rounded-full flex items-center justify-center"
                        ></span>
                    </span>
                    <span>Cart</span>
                </a>

                <button
                    x-data
                    type="button"
                    class="lg:hidden text-2xl text-brand-dark ml-2"
                    aria-label="Toggle navigation"
                    @click="$store.ui.toggle('mobileMenu')"
                >
                    <i class="fas" :class="$store.ui.mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
            </div>
        </div>
    </div>

    <x-nav.category-nav />
    <x-nav.mobile-menu />
</header>
