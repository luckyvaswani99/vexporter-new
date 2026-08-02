<x-layouts.storefront title="Vendor onboarding — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Become a VEXPORTER vendor</h1>
                <p class="text-gray-500">
                    Five short steps. Everything you submit is verified by our compliance team before your store goes live.
                </p>
            </div>

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

            <form
                method="POST"
                action="{{ route('vendor.onboarding.store') }}"
                enctype="multipart/form-data"
                x-data="{
                    step: 1,
                    steps: ['Company', 'Statutory', 'Catalogue', 'Certifications', 'Payout'],
                    documents: [{ id: 1 }],
                    nextDocumentId: 2,
                    next() { if (this.step < this.steps.length) this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }) },
                    back() { if (this.step > 1) this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }) },
                    addDocument() { this.documents.push({ id: this.nextDocumentId++ }) },
                    removeDocument(id) { this.documents = this.documents.filter(d => d.id !== id) },
                }"
                class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden"
            >
                @csrf

                <div class="border-b border-gray-100 px-6 sm:px-10 py-6">
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-3">
                        <template x-for="(label, index) in steps" :key="label">
                            <li class="flex items-center gap-2">
                                <span
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                                    :class="step > index + 1 ? 'bg-green-500 text-white' : (step === index + 1 ? 'bg-brand-red text-white' : 'bg-gray-100 text-gray-400')"
                                >
                                    <i class="fas fa-check" x-show="step > index + 1"></i>
                                    <span x-show="step <= index + 1" x-text="index + 1"></span>
                                </span>
                                <span class="text-sm font-medium" :class="step === index + 1 ? 'text-brand-dark' : 'text-gray-400'" x-text="label"></span>
                                <span class="hidden sm:block w-6 h-px bg-gray-200" x-show="index < steps.length - 1"></span>
                            </li>
                        </template>
                    </ol>
                </div>

                <div class="p-6 sm:p-10 space-y-6">
                    {{-- Step 1 — company --}}
                    <div x-show="step === 1" class="space-y-5">
                        <h2 class="text-xl font-bold text-brand-dark">Company details</h2>

                        <x-ui.field name="name" label="Store / brand name" required placeholder="MediChem Labs" />
                        <x-ui.field name="legal_name" label="Registered legal name" required placeholder="MediChem Laboratories Pvt Ltd" />

                        <x-ui.field name="about" label="About your company">
                            <textarea id="about" name="about" rows="4" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">{{ old('about') }}</textarea>
                        </x-ui.field>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <x-ui.field name="city" label="City" required />
                            <x-ui.field name="state" label="State" />
                            <x-ui.field name="country_code" label="Country">
                                <select id="country_code" name="country_code" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                    @foreach (\App\Support\Countries::NAMES as $code => $country)
                                        <option value="{{ $code }}" @selected(old('country_code', 'IN') === $code)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                    </div>

                    {{-- Step 2 — statutory --}}
                    <div x-show="step === 2" x-cloak class="space-y-5">
                        <h2 class="text-xl font-bold text-brand-dark">Statutory registration</h2>
                        <p class="text-sm text-gray-500">These are verified against government records before approval.</p>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-ui.field name="gst_number" label="GST number" required placeholder="27AAAAA0000A1Z5" />
                            <x-ui.field name="pan" label="PAN" required placeholder="AAAAA0000A" />
                            <x-ui.field name="iec_code" label="IEC code" required placeholder="0912345678" hint="Import Export Code issued by DGFT." />
                            <x-ui.field name="cin" label="CIN" placeholder="U12345MH2015PTC000000" />
                        </div>
                    </div>

                    {{-- Step 3 — catalogue --}}
                    <div x-show="step === 3" x-cloak class="space-y-5">
                        <h2 class="text-xl font-bold text-brand-dark">What do you supply?</h2>
                        <p class="text-sm text-gray-500">Pick the verticals and categories you manufacture or export.</p>

                        <div class="space-y-4">
                            @foreach ($verticals as $vertical)
                                <div class="rounded-2xl border-2 border-gray-100 p-5">
                                    <label class="flex items-center gap-3 mb-3">
                                        <input
                                            type="checkbox"
                                            name="verticals[]"
                                            value="{{ $vertical->id }}"
                                            class="rounded border-gray-300 text-brand-red focus:ring-brand-red"
                                            @checked(in_array($vertical->id, old('verticals', [])))
                                        >
                                        <span class="font-semibold text-brand-dark">
                                            <i class="fas {{ $vertical->icon }} text-brand-red mr-1"></i> {{ $vertical->name }}
                                        </span>
                                    </label>

                                    <div class="flex flex-wrap gap-2 pl-8">
                                        @foreach ($vertical->categories as $category)
                                            <label class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 cursor-pointer hover:border-brand-red transition">
                                                <input
                                                    type="checkbox"
                                                    name="categories[]"
                                                    value="{{ $category->id }}"
                                                    class="mr-1.5 rounded border-gray-300 text-brand-red focus:ring-brand-red"
                                                    @checked(in_array($category->id, old('categories', [])))
                                                >
                                                {{ $category->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Step 4 — certifications --}}
                    <div x-show="step === 4" x-cloak class="space-y-5">
                        <h2 class="text-xl font-bold text-brand-dark">Certifications &amp; licences</h2>
                        <p class="text-sm text-gray-500">
                            Upload WHO-GMP, FDA, EU-GMP, drug licence, BIS, ALMM, IEC, ISO — whatever applies to your
                            vertical. PDF or image, up to 5 MB each.
                        </p>

                        <template x-for="(document, index) in documents" :key="document.id">
                            <div class="rounded-2xl border-2 border-gray-100 p-5 grid sm:grid-cols-4 gap-4 items-end">
                                <div class="space-y-1.5 sm:col-span-1">
                                    <label class="block text-sm font-medium text-brand-dark">Certificate</label>
                                    <input type="text" :name="`documents[${index}][label]`" placeholder="WHO-GMP" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-brand-dark">Number</label>
                                    <input type="text" :name="`documents[${index}][number]`" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-brand-dark">Valid until</label>
                                    <input type="date" :name="`documents[${index}][expires_at]`" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-brand-dark">File</label>
                                    <div class="flex gap-2">
                                        <input type="file" :name="`documents[${index}][file]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-brand-red file:px-3 file:py-2 file:text-white">
                                        <button type="button" @click="removeDocument(document.id)" x-show="documents.length > 1" class="text-gray-300 hover:text-brand-red px-2" aria-label="Remove">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addDocument()" class="text-sm font-semibold text-brand-red hover:underline">
                            <i class="fas fa-plus text-xs"></i> Add another certificate
                        </button>
                    </div>

                    {{-- Step 5 — payout --}}
                    <div x-show="step === 5" x-cloak class="space-y-5">
                        <h2 class="text-xl font-bold text-brand-dark">Payout account</h2>
                        <p class="text-sm text-gray-500">
                            Settlements are released from escrow to this account after delivery is confirmed.
                            Account numbers are encrypted at rest.
                        </p>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-ui.field name="account_holder" label="Account holder name" required />
                            <x-ui.field name="account_no" label="Account number" required />
                            <x-ui.field name="ifsc" label="IFSC code" hint="Required for Indian accounts." />
                            <x-ui.field name="swift" label="SWIFT / BIC" />
                            <x-ui.field name="bank_name" label="Bank name" required />
                            <x-ui.field name="branch" label="Branch" />
                            <x-ui.field name="payout_currency" label="Settlement currency">
                                <select id="payout_currency" name="payout_currency" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                                    @foreach (['INR', 'USD', 'EUR', 'GBP', 'AED'] as $currency)
                                        <option value="{{ $currency }}" @selected(old('payout_currency', 'INR') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>

                        <label class="flex items-start gap-2 text-sm text-gray-600 pt-2">
                            <input type="checkbox" name="declaration" value="1" class="mt-1 rounded border-gray-300 text-brand-red focus:ring-brand-red" @checked(old('declaration'))>
                            <span>
                                I confirm the information and documents provided are accurate, and I accept the
                                <a href="{{ route('pages.show', 'vendor-guide') }}" class="text-brand-red hover:underline">vendor agreement</a>
                                including the {{ (int) config('vexporter.commission_percent') }}% platform commission.
                            </span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-100 px-6 sm:px-10 py-5 flex items-center justify-between bg-gray-50/60">
                    <button type="button" @click="back()" x-show="step > 1" class="text-sm font-semibold text-gray-500 hover:text-brand-dark">
                        <i class="fas fa-arrow-left text-xs"></i> Back
                    </button>
                    <span x-show="step === 1"></span>

                    <div class="flex gap-3">
                        <x-ui.button type="button" x-show="step < 5" @click="next()">
                            Continue <i class="fas fa-arrow-right"></i>
                        </x-ui.button>

                        <x-ui.button type="submit" x-show="step === 5" x-cloak size="lg">
                            Submit application <i class="fas fa-paper-plane"></i>
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</x-layouts.storefront>
