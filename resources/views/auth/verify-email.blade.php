<x-layouts.bare title="Verify your email — VEXPORTER">
    <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center mb-6">
        <i class="fas fa-envelope-circle-check text-2xl text-brand-red"></i>
    </div>

    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Verify your email</h1>
    <p class="text-gray-500 mb-8">
        We sent a verification link to <span class="font-medium text-brand-dark">{{ auth()->user()->email }}</span>.
        Click it to activate your account.
    </p>

    @if (session('status'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">Resend link</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="outline">Sign out</x-ui.button>
        </form>
    </div>
</x-layouts.bare>
