<x-layouts.storefront title="Request a quote — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Request a quote</h1>
            <p class="text-gray-500 mb-8">
                Tell us what you need. We notify matching verified vendors and their quotes land in your account.
            </p>

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-brand-red">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rfq.store') }}" class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 space-y-5">
                @csrf

                @if ($product)
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="flex items-center gap-4 rounded-2xl bg-brand-light p-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $product->image_gradient }} flex items-center justify-center shrink-0">
                            <i class="fas {{ $product->icon }} text-2xl {{ $product->icon_color }}"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-brand-dark">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->vendor->name }} · {{ $product->category->name }}</p>
                        </div>
                    </div>
                @else
                    <x-ui.field name="vertical_id" label="Vertical">
                        <select id="vertical_id" name="vertical_id" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                            <option value="">Any vertical</option>
                            @foreach ($verticals as $vertical)
                                <option value="{{ $vertical->id }}" @selected(old('vertical_id') == $vertical->id)>{{ $vertical->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                @endif

                <x-ui.field
                    name="title"
                    label="What are you sourcing?"
                    required
                    :value="$product?->name"
                    placeholder="Paracetamol API BP grade"
                />

                <x-ui.field name="description" label="Requirements" required>
                    <textarea
                        id="description" name="description" rows="5" required
                        class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none"
                        placeholder="Specifications, grade, packaging, certifications needed, target market..."
                    >{{ old('description') }}</textarea>
                </x-ui.field>

                <div class="grid sm:grid-cols-3 gap-4">
                    <x-ui.field name="qty" label="Quantity" type="number" required :value="old('qty', $product?->moq)" />

                    <x-ui.field name="unit" label="Unit" required>
                        <select id="unit" name="unit" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                            @foreach (['kg', 'ton', 'unit', 'set', 'pack', 'piece', 'litre', 'kw'] as $unit)
                                <option value="{{ $unit }}" @selected(old('unit', $product?->unit) === $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field name="target_price" label="Target price (USD)" type="number" step="0.01" hint="Optional" />
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <x-ui.field name="destination_country" label="Destination" required>
                        <select id="destination_country" name="destination_country" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                            @foreach ($countries as $code => $country)
                                <option value="{{ $code }}" @selected(old('destination_country') === $code)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field name="incoterm" label="Incoterm" required>
                        <select id="incoterm" name="incoterm" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                            @foreach (['FOB', 'CIF', 'EXW', 'DDP', 'DAP', 'CFR'] as $incoterm)
                                <option value="{{ $incoterm }}" @selected(old('incoterm', 'FOB') === $incoterm)>{{ $incoterm }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field name="delivery_by" label="Needed by" type="date" hint="Optional" />
                </div>

                <x-ui.button type="submit" size="lg" class="w-full">
                    Send quote request <i class="fas fa-paper-plane"></i>
                </x-ui.button>
            </form>
        </div>
    </section>
</x-layouts.storefront>
