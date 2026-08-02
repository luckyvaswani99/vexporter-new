<x-layouts.storefront title="Sell on VEXPORTER — Become a vendor">
    <section class="gradient-hero relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <div class="absolute top-10 left-10 w-72 h-72 bg-brand-red rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center text-white">
                <div>
                    <p class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium mb-6 border border-white/20">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        Zero listing fees · {{ (int) config('vexporter.commission_percent') }}% commission only
                    </p>

                    <h1 class="text-4xl sm:text-5xl font-extrabold font-display leading-tight mb-6">
                        Sell to buyers in<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-orange-400">150+ countries</span>
                    </h1>

                    <p class="text-gray-300 text-lg mb-8 max-w-lg leading-relaxed">
                        Join verified Indian manufacturers exporting pharma, solar and general trade products —
                        with escrow-protected payments, logistics and export documentation handled for you.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        @auth
                            <x-ui.button :href="route('vendor.onboarding.create')" size="lg">
                                Start your application <i class="fas fa-arrow-right"></i>
                            </x-ui.button>
                        @else
                            <x-ui.button :href="route('register', ['as' => 'vendor'])" size="lg">
                                Register as vendor <i class="fas fa-arrow-right"></i>
                            </x-ui.button>
                            <x-ui.button :href="route('login')" variant="ghost" size="lg">Sign in</x-ui.button>
                        @endauth
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 space-y-5">
                        @foreach ([
                            ['fa-file-signature', 'Apply in 5 steps', 'Company, statutory, catalogue, certifications, payout.'],
                            ['fa-user-shield', 'Compliance review', 'GST, PAN, IEC and certificates verified by our team.'],
                            ['fa-store', 'Store goes live', 'List products, receive RFQs and orders from global buyers.'],
                            ['fa-money-bill-transfer', 'Get paid', 'Escrow released after delivery, weekly payout cycle.'],
                        ] as $index => [$icon, $title, $body])
                            <div class="flex gap-4">
                                <div class="w-11 h-11 rounded-xl bg-brand-red/20 border border-brand-red/30 flex items-center justify-center shrink-0">
                                    <i class="fas {{ $icon }} text-brand-red"></i>
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $index + 1 }}. {{ $title }}</p>
                                    <p class="text-sm text-gray-400">{{ $body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.section-heading
                eyebrow="Why sell with us"
                title="Built for exporters, not marketplaces"
                subtitle="Everything an Indian manufacturer needs to sell internationally — without building it yourself."
                class="mb-14"
            />

            <div class="grid md:grid-cols-3 gap-8">
                @foreach ([
                    ['fa-hand-holding-dollar', 'Escrow payments', 'Buyers pay upfront into escrow. Funds are released to you once delivery is confirmed — no chasing invoices.'],
                    ['fa-file-contract', 'Export paperwork', 'Proforma and commercial invoices, packing lists and COO generated from your orders automatically.'],
                    ['fa-chart-line', 'Real analytics', 'Track GMV, conversion, RFQ response times and payout balance from one dashboard.'],
                    ['fa-globe', 'Global demand', 'Buyers across Africa, the Middle East, Europe and Latin America already sourcing on VEXPORTER.'],
                    ['fa-shield-halved', 'Verified marketplace', 'Every vendor is KYC-checked, so your listings sit alongside credible suppliers only.'],
                    ['fa-headset', 'Dedicated support', 'An account manager for onboarding, compliance questions and dispute resolution.'],
                ] as [$icon, $title, $body])
                    <div class="card-hover bg-white rounded-2xl border border-gray-100 p-7">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mb-4">
                            <i class="fas {{ $icon }} text-brand-red text-xl"></i>
                        </div>
                        <h3 class="font-bold text-brand-dark mb-2">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-brand-light">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.section-heading eyebrow="Before you apply" title="What you will need" class="mb-10" />

            <ul class="space-y-4">
                @foreach ([
                    'GST number, PAN and IEC (Import Export Code)',
                    'Company registration details (CIN, if applicable)',
                    'Vertical-specific certificates — WHO-GMP / FDA / drug licence for pharma, BIS / ALMM / IEC for solar',
                    'Bank account for settlements (IFSC for Indian accounts, SWIFT for others)',
                ] as $item)
                    <li class="flex items-start gap-3 bg-white rounded-xl border border-gray-100 px-5 py-4">
                        <i class="fas fa-circle-check text-green-500 mt-0.5"></i>
                        <span class="text-sm text-gray-600">{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10 text-center">
                @auth
                    <x-ui.button :href="route('vendor.onboarding.create')" size="lg">
                        Start your application <i class="fas fa-arrow-right"></i>
                    </x-ui.button>
                @else
                    <x-ui.button :href="route('register', ['as' => 'vendor'])" size="lg">
                        Register as vendor <i class="fas fa-arrow-right"></i>
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </section>
</x-layouts.storefront>
