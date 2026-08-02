<x-layouts.storefront title="Application status — VEXPORTER">
    @php
        $states = [
            'pending' => ['icon' => 'fa-hourglass-half', 'tone' => 'bg-yellow-50 text-yellow-600', 'title' => 'Under review', 'body' => 'Our compliance team is verifying your documents. This usually takes up to two business days.'],
            'approved' => ['icon' => 'fa-circle-check', 'tone' => 'bg-green-50 text-green-600', 'title' => 'Approved', 'body' => 'Your store is live. You can start listing products and receiving orders.'],
            'rejected' => ['icon' => 'fa-circle-xmark', 'tone' => 'bg-red-50 text-brand-red', 'title' => 'Needs attention', 'body' => 'We could not approve the application yet — see the reason below, then get in touch to resubmit.'],
            'suspended' => ['icon' => 'fa-ban', 'tone' => 'bg-gray-100 text-gray-600', 'title' => 'Suspended', 'body' => 'This store is temporarily suspended. Contact support for details.'],
        ];

        $state = $states[$vendor->status] ?? $states['pending'];
    @endphp

    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-8 sm:p-10">
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 rounded-2xl {{ $state['tone'] }} flex items-center justify-center shrink-0">
                        <i class="fas {{ $state['icon'] }} text-xl"></i>
                    </div>

                    <div>
                        <h1 class="text-2xl font-extrabold font-display text-brand-dark mb-1">{{ $state['title'] }}</h1>
                        <p class="text-gray-500">{{ $state['body'] }}</p>
                    </div>
                </div>

                @if ($vendor->status === 'rejected' && $vendor->rejection_reason)
                    <div class="mt-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-brand-red">
                        <span class="font-semibold">Reason:</span> {{ $vendor->rejection_reason }}
                    </div>
                @endif

                <dl class="mt-8 grid sm:grid-cols-2 gap-5 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wider mb-1">Store</dt>
                        <dd class="font-semibold text-brand-dark">{{ $vendor->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wider mb-1">Legal entity</dt>
                        <dd class="font-semibold text-brand-dark">{{ $vendor->legal_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wider mb-1">Location</dt>
                        <dd class="font-semibold text-brand-dark">{{ $vendor->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wider mb-1">Submitted</dt>
                        <dd class="font-semibold text-brand-dark">{{ $vendor->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <div class="mt-8">
                    <h2 class="font-bold text-brand-dark mb-3">Documents</h2>

                    <ul class="divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden">
                        @foreach ($vendor->documents as $document)
                            <li class="flex items-center justify-between px-4 py-3 text-sm">
                                <span class="flex items-center gap-3">
                                    <i class="fas fa-file-lines text-gray-300"></i>
                                    <span class="text-brand-dark">{{ $document->label ?? $document->type }}</span>
                                    @if ($document->number)
                                        <span class="text-xs text-gray-400">{{ $document->number }}</span>
                                    @endif
                                </span>

                                <span @class([
                                    'text-[11px] font-semibold px-2.5 py-1 rounded-full',
                                    'bg-green-50 text-green-600' => $document->status === 'verified',
                                    'bg-yellow-50 text-yellow-600' => $document->status === 'pending',
                                    'bg-red-50 text-brand-red' => $document->status === 'rejected',
                                ])>
                                    {{ ucfirst($document->status) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($vendor->isApproved())
                        <x-ui.button :href="route('account.dashboard')">Go to dashboard</x-ui.button>
                    @endif

                    <x-ui.button :href="route('contact')" variant="outline">Contact support</x-ui.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
