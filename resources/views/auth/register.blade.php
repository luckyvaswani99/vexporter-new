<x-layouts.bare title="Create your account — VEXPORTER">
    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Create your account</h1>
    <p class="text-gray-500 mb-8">Join verified buyers and manufacturers trading across 150+ countries.</p>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" x-data="{ accountType: '{{ old('account_type', $accountType) }}' }">
        @csrf

        <div>
            <span class="block text-sm font-medium text-brand-dark mb-2">I want to</span>

            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['value' => 'buyer', 'title' => 'Buy', 'sub' => 'Source products', 'icon' => 'fa-cart-shopping'],
                    ['value' => 'vendor', 'title' => 'Sell', 'sub' => 'List my products', 'icon' => 'fa-store'],
                ] as $option)
                    <label
                        class="cursor-pointer rounded-xl border-2 p-4 transition"
                        :class="accountType === '{{ $option['value'] }}' ? 'border-brand-red bg-red-50/50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="radio" name="account_type" value="{{ $option['value'] }}" x-model="accountType" class="sr-only">
                        <i class="fas {{ $option['icon'] }} text-lg mb-2" :class="accountType === '{{ $option['value'] }}' ? 'text-brand-red' : 'text-gray-400'"></i>
                        <span class="block font-semibold text-brand-dark text-sm">{{ $option['title'] }}</span>
                        <span class="block text-xs text-gray-500">{{ $option['sub'] }}</span>
                    </label>
                @endforeach
            </div>

            @error('account_type')
                <p class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.field name="name" label="Full name" icon="fa-user" required autofocus autocomplete="name" />
        <x-ui.field name="email" label="Work email" type="email" icon="fa-envelope" required autocomplete="email" />
        <x-ui.field name="phone" label="Phone" type="tel" icon="fa-phone" placeholder="+91 98765 43210" autocomplete="tel" />

        <template x-if="accountType === 'buyer'">
            <div class="space-y-5">
                <x-ui.field name="company_name" label="Company name" icon="fa-building" hint="Optional — helps us verify your buyer profile faster." />
            </div>
        </template>

        <template x-if="accountType === 'vendor'">
            <p class="rounded-xl bg-brand-light border border-gray-200 px-4 py-3 text-xs text-gray-600">
                <i class="fas fa-circle-info text-brand-red mr-1"></i>
                After signing up you will complete a short onboarding — company details, certifications and payout
                information. Our team reviews every vendor before products go live.
            </p>
        </template>

        <x-ui.field name="password" label="Password" type="password" icon="fa-lock" required autocomplete="new-password" hint="Minimum 8 characters." />
        <x-ui.field name="password_confirmation" label="Confirm password" type="password" icon="fa-lock" required autocomplete="new-password" />

        <div>
            <label class="flex items-start gap-2 text-sm text-gray-600">
                <input type="checkbox" name="terms" value="1" class="mt-1 rounded border-gray-300 text-brand-red focus:ring-brand-red" @checked(old('terms'))>
                <span>
                    I agree to the
                    <a href="{{ route('pages.show', 'terms') }}" class="text-brand-red hover:underline">Terms of Service</a>
                    and
                    <a href="{{ route('pages.show', 'privacy') }}" class="text-brand-red hover:underline">Privacy Policy</a>.
                </span>
            </label>

            @error('terms')
                <p class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">
            Create account <i class="fas fa-arrow-right"></i>
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand-red hover:underline">Sign in</a>
    </p>
</x-layouts.bare>
