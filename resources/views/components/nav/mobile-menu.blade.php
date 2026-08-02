<div
    x-data
    x-show="$store.ui.mobileMenu"
    x-cloak
    x-collapse
    class="lg:hidden border-t border-gray-100 bg-white"
>
    <div class="px-4 py-4 space-y-2">
        <div class="mb-3">
            <x-search.bar :compact="true" />
        </div>

        @foreach ($navVerticals as $vertical)
            <a href="{{ route('verticals.show', $vertical['slug']) }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-brand-red rounded-lg font-medium">
                <i class="fas {{ $vertical['icon'] }} w-5 text-brand-red"></i> {{ $vertical['name'] }}
            </a>
        @endforeach

        <a href="{{ route('vendors.index') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-brand-red rounded-lg font-medium">
            <i class="fas fa-store w-5 text-brand-red"></i> Vendors
        </a>
        <a href="{{ route('deals') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-brand-red rounded-lg font-medium">
            <i class="fas fa-bolt w-5 text-brand-red"></i> Hot Deals
        </a>
        @if (setting('header.show_wishlist', true))
            <a href="{{ route('account.wishlist') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-brand-red rounded-lg font-medium">
                <i class="far fa-heart w-5 text-brand-red"></i> Wishlist
            </a>
        @endif
        <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-brand-red rounded-lg font-medium">
            <i class="far fa-user w-5 text-brand-red"></i> {{ auth()->check() ? 'My Account' : 'Sign in' }}
        </a>
        <a href="{{ route('become-vendor') }}" class="block px-4 py-3 mt-2 text-center btn-primary text-white rounded-lg font-semibold">
            Sell on VEXPORTER
        </a>
    </div>
</div>
