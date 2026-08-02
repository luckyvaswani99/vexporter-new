<x-layouts.storefront title="Checkout — VEXPORTER">
    <section class="py-10 bg-brand-light min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-8">Checkout</h1>

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-brand-red">
                    <p class="font-semibold mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-3 gap-8">
                @csrf

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8">
                        <h2 class="font-bold text-brand-dark text-lg mb-5">Shipping address</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-ui.field name="contact_name" label="Contact name" required :value="$address?->contact_name ?? auth()->user()->name" />
                            <x-ui.field name="company" label="Company" :value="$address?->company ?? auth()->user()->buyerProfile?->company_name" />
                            <x-ui.field name="line1" label="Address line 1" required :value="$address?->line1" class="sm:col-span-2" />
                            <x-ui.field name="line2" label="Address line 2" :value="$address?->line2" class="sm:col-span-2" />
                            <x-ui.field name="city" label="City" required :value="$address?->city" />
                            <x-ui.field name="state" label="State / region" :value="$address?->state" />
                            <x-ui.field name="postcode" label="Postcode" :value="$address?->postcode" />

                            <x-ui.field name="country_code" label="Country" required>
                                <select id="country_code" name="country_code" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                    @foreach ($countries as $code => $country)
                                        <option value="{{ $code }}" @selected(old('country_code', $address?->country_code) === $code)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>

                            <x-ui.field name="phone" label="Phone" required :value="$address?->phone ?? auth()->user()->phone" />
                            <x-ui.field name="tax_id" label="Tax / VAT ID" :value="$address?->tax_id" hint="Used on your commercial invoice." />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-600 mt-4">
                            <input type="checkbox" name="save_address" value="1" checked class="rounded border-gray-300 text-brand-red focus:ring-brand-red">
                            Save this address for future orders
                        </label>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8">
                        <h2 class="font-bold text-brand-dark text-lg mb-5">Trade terms</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-ui.field name="incoterm" label="Incoterm" required>
                                <select id="incoterm" name="incoterm" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                    @foreach (['FOB' => 'FOB — Free on board', 'CIF' => 'CIF — Cost, insurance & freight', 'EXW' => 'EXW — Ex works', 'DDP' => 'DDP — Delivered duty paid', 'DAP' => 'DAP — Delivered at place', 'CFR' => 'CFR — Cost and freight'] as $code => $label)
                                        <option value="{{ $code }}" @selected(old('incoterm', 'FOB') === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>

                            <x-ui.field name="notes" label="Notes for vendors" class="sm:col-span-2">
                                <textarea id="notes" name="notes" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none" placeholder="Packaging, labelling or documentation requirements...">{{ old('notes') }}</textarea>
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8">
                        <h2 class="font-bold text-brand-dark text-lg mb-5">Payment</h2>

                        @php($methods = \App\Support\PaymentMethods::enabled())

                        @if ($methods)
                            <p class="text-sm text-gray-600 mb-4">
                                Choose how to pay on the next step — you will not be charged until you confirm.
                            </p>

                            <ul class="grid sm:grid-cols-2 gap-3">
                                @foreach ($methods as $method)
                                    <li class="flex items-start gap-3 rounded-xl border-2 border-gray-100 p-4">
                                        @if ($method['icon'] ?? null)
                                            <i class="{{ $method['icon'] }} text-brand-red mt-0.5"></i>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-brand-dark text-sm">{{ $method['label'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $method['blurb'] ?? '' }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-600">
                                Our team will contact you with settlement instructions once the vendors confirm.
                            </p>
                        @endif

                        <p class="text-xs text-gray-400 mt-4">
                            <i class="fas fa-shield-halved text-brand-red mr-1"></i>
                            Funds are held in escrow and released to the vendor only after you confirm delivery.
                        </p>
                    </div>
                </div>

                <aside>
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-28">
                        <h2 class="font-bold text-brand-dark mb-5">Order summary</h2>

                        <div class="space-y-4 mb-5 max-h-72 overflow-y-auto pr-1">
                            @foreach ($groups as $group)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">{{ $group['vendor']->name }}</p>
                                    @foreach ($group['items'] as $item)
                                        <div class="flex justify-between text-sm py-1">
                                            <span class="text-gray-600 truncate pr-3">
                                                {{ $item->snapshot['name'] ?? $item->product->name }}
                                                <span class="text-gray-400">× {{ number_format($item->qty) }}</span>
                                            </span>
                                            <span class="font-medium text-brand-dark whitespace-nowrap">
                                                {{ \App\Support\Money::format($item->unit_price * $item->qty) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <dl class="space-y-2 text-sm border-t border-gray-100 pt-4">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Subtotal</dt>
                                <dd class="font-semibold text-brand-dark">{{ \App\Support\Money::format($subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Freight estimate</dt>
                                <dd class="font-semibold text-brand-dark">{{ \App\Support\Money::format($shipping) }}</dd>
                            </div>
                            <div class="flex justify-between text-base border-t border-gray-100 pt-3 mt-3">
                                <dt class="font-bold text-brand-dark">Total</dt>
                                <dd class="font-extrabold text-brand-red">{{ \App\Support\Money::format($subtotal + $shipping) }}</dd>
                            </div>
                        </dl>

                        <x-ui.button type="submit" size="lg" class="w-full mt-6">
                            Place order <i class="fas fa-arrow-right"></i>
                        </x-ui.button>

                        <p class="text-xs text-gray-400 mt-3 text-center">
                            By placing this order you accept the
                            <a href="{{ route('pages.show', 'terms') }}" class="text-brand-red hover:underline">terms of service</a>.
                        </p>
                    </div>
                </aside>
            </form>
        </div>
    </section>
</x-layouts.storefront>
