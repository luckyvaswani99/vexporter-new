<x-layouts.storefront :title="$title . ' — VEXPORTER'" :description="$subtitle">
    <div class="bg-brand-light border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <x-nav.breadcrumbs :items="$breadcrumbs" class="mb-4" />

            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold font-display text-brand-dark">{{ $title }}</h1>

                    @if ($intro ?? null)
                        <div class="prose-storefront text-gray-500 mt-2 max-w-2xl">{!! $intro !!}</div>
                    @elseif ($subtitle)
                        <p class="text-gray-500 mt-2 max-w-2xl">{{ $subtitle }}</p>
                    @endif
                </div>

                <p class="text-sm text-gray-500">
                    <span class="font-semibold text-brand-dark">{{ number_format($facets['total']) }}</span> products
                </p>
            </div>
        </div>
    </div>

    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-4 gap-8">
                {{-- Desktop filters --}}
                <aside class="hidden lg:block">
                    <div class="sticky top-28 bg-white rounded-2xl border border-gray-100 p-6">
                        <x-storefront.filters-heading />
                        @include('storefront.partials.filters', ['facets' => $facets, 'sorts' => $sorts])
                    </div>
                </aside>

                <div class="lg:col-span-3">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                x-data
                                type="button"
                                class="lg:hidden inline-flex items-center gap-2 rounded-full border-2 border-gray-200 px-4 py-2 text-sm font-medium"
                                @click="$store.ui.toggle('filterDrawer')"
                            >
                                <i class="fas fa-sliders"></i> Filters
                            </button>

                            @foreach ($activeFilters as $key => $value)
                                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 text-brand-red px-3 py-1.5 text-xs font-medium">
                                    {{ str($key)->headline() }}: {{ is_array($value) ? implode(', ', $value) : $value }}
                                </span>
                            @endforeach
                        </div>

                        <form method="GET" class="flex items-center gap-3">
                            @foreach (request()->except(['sort', 'page', 'per_page']) as $key => $value)
                                @foreach ((array) $value as $item)
                                    <input type="hidden" name="{{ $key }}{{ is_array($value) ? '[]' : '' }}" value="{{ $item }}">
                                @endforeach
                            @endforeach

                            <label for="sort" class="sr-only">Sort by</label>
                            <select
                                id="sort" name="sort" onchange="this.form.submit()"
                                class="rounded-full border-2 border-gray-200 px-4 py-2 text-sm focus:border-brand-red focus:outline-none"
                            >
                                @foreach ($sorts as $value => $label)
                                    <option value="{{ $value }}" @selected(request()->query('sort') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <label for="per_page" class="sr-only">Per page</label>
                            <select
                                id="per_page" name="per_page" onchange="this.form.submit()"
                                class="hidden sm:block rounded-full border-2 border-gray-200 px-4 py-2 text-sm focus:border-brand-red focus:outline-none"
                            >
                                @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}" @selected((int) request()->query('per_page') === $option)>{{ $option }} / page</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @include('storefront.partials.product-grid', ['products' => $products])
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile filter drawer --}}
    <div
        x-data
        x-show="$store.ui.filterDrawer"
        x-cloak
        class="lg:hidden fixed inset-0 z-[70]"
        @keydown.escape.window="$store.ui.close('filterDrawer')"
    >
        <div class="absolute inset-0 bg-black/40" @click="$store.ui.close('filterDrawer')"></div>

        <div class="absolute inset-y-0 left-0 w-[88%] max-w-sm bg-white overflow-y-auto p-6" x-transition>
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-bold text-brand-dark text-lg">Filters</h2>
                <button type="button" @click="$store.ui.close('filterDrawer')" aria-label="Close filters">
                    <i class="fas fa-xmark text-gray-400"></i>
                </button>
            </div>

            @include('storefront.partials.filters', ['facets' => $facets, 'sorts' => $sorts])
        </div>
    </div>
</x-layouts.storefront>
