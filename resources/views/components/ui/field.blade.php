@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'icon' => null,
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-brand-dark">
        {{ $label }}
        @if ($required)
            <span class="text-brand-red" aria-hidden="true">*</span>
        @endif
    </label>

    <div class="relative">
        @if ($icon)
            <i class="fas {{ $icon }} absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
        @endif

        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            <input
                id="{{ $name }}"
                name="{{ $name }}"
                type="{{ $type }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                @if ($required) required @endif
                {{ $attributes->merge([
                    'class' => 'w-full rounded-xl border-2 py-3 text-sm transition-colors focus:outline-none '
                        . ($icon ? 'pl-11 pr-4 ' : 'px-4 ')
                        . ($errors->has($name)
                            ? 'border-brand-red bg-red-50/40 focus:border-brand-red'
                            : 'border-gray-200 bg-gray-50 focus:border-brand-red'),
                ]) }}
            >
        @endif
    </div>

    @error($name)
        <p class="text-xs text-brand-red flex items-center gap-1.5">
            <i class="fas fa-circle-exclamation"></i> {{ $message }}
        </p>
    @else
        @if ($hint)
            <p class="text-xs text-gray-400">{{ $hint }}</p>
        @endif
    @enderror
</div>
