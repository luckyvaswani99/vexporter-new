@props(['rfq'])

@if (! empty($rfq->attachments))
    <div class="flex flex-wrap gap-3 mb-6">
        @foreach ($rfq->attachments as $file)
            @php
                $path = is_array($file) ? ($file['path'] ?? null) : $file;
                $name = is_array($file) ? ($file['name'] ?? basename((string) $path)) : basename((string) $file);
                $isImage = $path && preg_match('/\.(jpe?g|png|webp)$/i', $path);
            @endphp
            @continue(! $path)
            <a href="{{ asset('storage/'.$path) }}" target="_blank" rel="noopener" class="group block w-24 text-center">
                @if ($isImage)
                    <img src="{{ asset('storage/'.$path) }}" alt="{{ $name }}" class="w-24 h-24 object-cover rounded-xl border border-gray-100 group-hover:border-brand-red transition">
                @else
                    <span class="w-24 h-24 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center text-3xl text-brand-red group-hover:border-brand-red transition"><i class="fas fa-file-pdf"></i></span>
                @endif
                <span class="block mt-1 text-[11px] text-gray-500 truncate">{{ $name }}</span>
            </a>
        @endforeach
    </div>
@endif
