@props(['compact' => false])

<div x-data="searchAutocomplete" @click.outside="open = false" class="relative w-full">
    <form action="{{ route('search') }}" method="GET" class="relative w-full">
        <label for="q-{{ $compact ? 'mobile' : 'desktop' }}" class="sr-only">Search products, brands, vendors</label>

        <input
            id="q-{{ $compact ? 'mobile' : 'desktop' }}"
            type="search"
            name="q"
            x-model="query"
            @input="onInput()"
            @focus="hasResults && (open = true)"
            autocomplete="off"
            placeholder="{{ setting('header.search_placeholder') }}"
            class="w-full {{ $compact ? 'pl-4 pr-12 py-3' : 'pl-5 pr-32 py-3.5' }} rounded-full border-2 border-gray-200 focus:border-brand-red focus:outline-none text-sm transition-colors bg-gray-50"
        >

        @unless ($compact)
            <label for="vertical-filter" class="sr-only">Category</label>
            <select
                id="vertical-filter"
                name="vertical"
                x-model="vertical"
                class="absolute right-20 top-1/2 -translate-y-1/2 bg-transparent text-sm text-gray-500 border-l border-gray-200 pl-3 pr-8 focus:outline-none cursor-pointer"
            >
                <option value="">All Categories</option>
                @foreach ($navVerticals as $vertical)
                    <option value="{{ $vertical['slug'] }}">{{ $vertical['name'] }}</option>
                @endforeach
            </select>
        @endunless

        <button
            type="submit"
            aria-label="Search"
            class="absolute {{ $compact ? 'right-1.5 w-9 h-9' : 'right-2 w-12 h-10' }} top-1/2 -translate-y-1/2 btn-primary text-white rounded-full flex items-center justify-center hover:shadow-lg transition"
        >
            <i class="fas fa-search" x-show="! loading"></i>
            <i class="fas fa-circle-notch fa-spin" x-show="loading" x-cloak></i>
        </button>
    </form>

    <div
        x-show="open && hasResults"
        x-cloak
        x-transition.opacity
        class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden max-h-[28rem] overflow-y-auto"
    >
        <template x-if="results.categories.length">
            <div class="p-3 border-b border-gray-50">
                <p class="px-2 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Categories</p>
                <template x-for="item in results.categories" :key="'c' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-brand-light text-sm">
                        <i class="fas fa-folder-open text-gray-300"></i>
                        <span x-text="item.name"></span>
                    </a>
                </template>
            </div>
        </template>

        <template x-if="results.products.length">
            <div class="p-3 border-b border-gray-50">
                <p class="px-2 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Products</p>
                <template x-for="item in results.products" :key="'p' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-brand-light">
                        <span class="w-9 h-9 rounded-lg bg-brand-light flex items-center justify-center">
                            <i class="fas fa-box text-gray-300 text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm text-brand-dark truncate" x-text="item.name"></span>
                            <span class="block text-xs text-gray-400" x-text="item.vendor"></span>
                        </span>
                        <span class="text-sm font-semibold text-brand-red" x-text="item.price"></span>
                    </a>
                </template>
            </div>
        </template>

        <template x-if="results.vendors.length">
            <div class="p-3">
                <p class="px-2 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Vendors</p>
                <template x-for="item in results.vendors" :key="'v' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-brand-light text-sm">
                        <i class="fas fa-store text-gray-300"></i>
                        <span x-text="item.name"></span>
                    </a>
                </template>
            </div>
        </template>
    </div>
</div>
