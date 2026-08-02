<x-layouts.bare title="Reset password — VEXPORTER">
    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Forgot your password?</h1>
    <p class="text-gray-500 mb-8">Enter your email and we will send you a secure reset link.</p>

    @if (session('status'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-ui.field name="email" label="Email address" type="email" icon="fa-envelope" required autofocus />

        <x-ui.button type="submit" size="lg" class="w-full">Send reset link</x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-brand-red hover:underline">
            <i class="fas fa-arrow-left text-xs"></i> Back to sign in
        </a>
    </p>
</x-layouts.bare>
