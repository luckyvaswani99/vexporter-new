<x-layouts.bare title="Choose a new password — VEXPORTER">
    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Choose a new password</h1>
    <p class="text-gray-500 mb-8">Pick something strong — this protects your orders and payouts.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.field name="email" label="Email address" type="email" icon="fa-envelope" :value="$email" required />
        <x-ui.field name="password" label="New password" type="password" icon="fa-lock" required autocomplete="new-password" />
        <x-ui.field name="password_confirmation" label="Confirm new password" type="password" icon="fa-lock" required autocomplete="new-password" />

        <x-ui.button type="submit" size="lg" class="w-full">Reset password</x-ui.button>
    </form>
</x-layouts.bare>
