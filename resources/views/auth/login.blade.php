<x-layouts.bare title="Sign in — VEXPORTER">
    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Welcome back</h1>
    <p class="text-gray-500 mb-8">Sign in to manage your orders, quotes and shipments.</p>

    @if (session('status'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <x-ui.field name="email" label="Email address" type="email" icon="fa-envelope" required autofocus autocomplete="username" />
        <x-ui.field name="password" label="Password" type="password" icon="fa-lock" required autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-red focus:ring-brand-red">
                Remember me
            </label>

            <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-red hover:underline">Forgot password?</a>
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">
            Sign in <i class="fas fa-arrow-right"></i>
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        New to VEXPORTER?
        <a href="{{ route('register') }}" class="font-semibold text-brand-red hover:underline">Create an account</a>
    </p>

    <p class="mt-3 text-center text-sm text-gray-500">
        Want to sell?
        <a href="{{ route('become-vendor') }}" class="font-semibold text-brand-dark hover:underline">Become a vendor</a>
    </p>
</x-layouts.bare>
