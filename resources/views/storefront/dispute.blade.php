<x-layouts.storefront title="Dispute #{{ $dispute->reference }} - VEXPORTER">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('account.orders.show', $dispute->order) }}" class="text-xs text-slate-500 hover:text-brand-red">
                    &larr; Back to Order #{{ $dispute->order->reference }}
                </a>
                <h1 class="text-2xl font-bold text-slate-900 font-display mt-1">Dispute {{ $dispute->reference }}</h1>
                <p class="text-xs text-slate-500">Sub-Order: {{ $dispute->subOrder->reference }} (Vendor: {{ $dispute->vendor->name }})</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                {{ str($dispute->status)->headline() }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm space-y-3">
            <div class="text-xs font-semibold text-slate-400 uppercase">Reason for Dispute</div>
            <div class="text-base font-bold text-slate-900">{{ $dispute->reason }}</div>
            <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $dispute->description }}</p>
        </div>

        {{-- Dispute Message Thread --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm mb-8 space-y-6">
            <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-3">Arbitration Communication Thread</h3>

            <div class="space-y-4">
                @foreach($dispute->messages as $msg)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                            {{ substr($msg->user->name, 0, 2) }}
                        </div>
                        <div class="flex-1 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-900 text-xs">{{ $msg->user->name }} <span class="text-slate-400 font-normal">({{ ucfirst($msg->user->type) }})</span></span>
                                <span class="text-slate-400 text-xs">{{ $msg->created_at->format('d M, H:i') }}</span>
                            </div>
                            <p class="text-slate-700">{{ $msg->message }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(! in_array($dispute->status, [\App\Models\Dispute::STATUS_RESOLVED_REFUND, \App\Models\Dispute::STATUS_RESOLVED_RELEASED, \App\Models\Dispute::STATUS_REJECTED]))
                <form action="{{ route('account.disputes.reply', $dispute) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                    @csrf
                    <textarea name="message" rows="3" required placeholder="Type reply or add evidence details..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-brand-red"></textarea>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition">
                        Post Response
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.storefront>
