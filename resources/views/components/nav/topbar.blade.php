@php
    $phone = setting('header.phone');
    $email = setting('header.email');
@endphp

@if (setting('header.show_topbar', true))
    <div class="bg-brand-dark text-white text-sm py-2.5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-6">
                @if ($phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex items-center gap-2 hover:text-brand-red transition">
                        <i class="fas fa-phone-alt text-brand-red"></i> {{ $phone }}
                    </a>
                @endif

                @if ($email)
                    <a href="mailto:{{ $email }}" class="hidden sm:flex items-center gap-2 hover:text-brand-red transition">
                        <i class="fas fa-envelope text-brand-red"></i> {{ $email }}
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-4">
                @foreach (setting('header.links', []) as $index => $link)
                    @if ($index > 0)
                        <span class="text-gray-500" aria-hidden="true">|</span>
                    @endif

                    <a href="{{ $link['url'] ?? '#' }}" class="hover:text-brand-red transition">{{ $link['label'] ?? '' }}</a>
                @endforeach
            </div>
        </div>
    </div>
@endif
