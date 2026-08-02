<nav class="hidden lg:block border-t border-gray-100" aria-label="Categories">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-1 py-3">
            <div x-data="dropdown" class="relative mr-4" @click.outside="close()">
                <button
                    type="button"
                    @click="toggle()"
                    class="flex items-center gap-2 bg-brand-red text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-brand-red-dark transition"
                    :aria-expanded="open"
                >
                    <i class="fas fa-th-large"></i> All Categories
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition.origin.top.left
                    class="absolute left-0 mt-2 w-[44rem] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 grid grid-cols-3 gap-6"
                >
                    @foreach ($navVerticals as $vertical)
                        <div>
                            <a href="{{ route('verticals.show', $vertical['slug']) }}" class="flex items-center gap-2 font-bold text-brand-dark mb-3 hover:text-brand-red transition">
                                <i class="fas {{ $vertical['icon'] }} text-brand-red"></i> {{ $vertical['name'] }}
                            </a>
                            <ul class="space-y-2 text-sm">
                                @foreach ($vertical['categories'] as $category)
                                    <li>
                                        <a href="{{ route('categories.show', $category['slug']) }}" class="text-gray-500 hover:text-brand-red transition">{{ $category['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('verticals.show', 'main-store') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">Main Store</a>
            <a href="{{ route('verticals.show', 'pharma') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">Pharma</a>
            <a href="{{ route('verticals.show', 'solar') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">Solar</a>
            <a href="{{ route('vendors.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">Vendors</a>
            <a href="{{ route('deals') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">Hot Deals</a>
            <a href="{{ route('new-arrivals') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-red-50 rounded-lg transition">New Arrivals</a>

            <div class="ml-auto flex items-center gap-4 text-sm">
                <span class="text-gray-400" aria-hidden="true">|</span>
                <a href="{{ route('deals') }}" class="text-brand-red font-semibold hover:underline">
                    Flash Sale <i class="fas fa-bolt ml-1 animate-pulse"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
