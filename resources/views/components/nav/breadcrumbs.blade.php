@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'text-sm']) }} aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2 text-gray-400">
        <li>
            <a href="{{ route('home') }}" class="hover:text-brand-red transition">Home</a>
        </li>

        @foreach ($items as $item)
            <li aria-hidden="true"><i class="fas fa-chevron-right text-[10px]"></i></li>
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-brand-red transition">{{ $item['label'] }}</a>
                @else
                    <span class="text-brand-dark font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
