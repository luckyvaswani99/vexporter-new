<x-filament-widgets::widget>
    <x-filament::section heading="Get your store ready" description="Three quick steps and buyers can find you.">
        {{-- Inline styles: the panel ships a fixed stylesheet, so arbitrary utility classes would not exist. --}}
        <ol style="display:grid;gap:.75rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));list-style:none;margin:0;padding:0">
            @foreach ($steps as $step)
                <li style="display:flex;flex-direction:column;gap:.75rem;border:1px solid rgba(128,128,128,.25);border-radius:.75rem;padding:1rem">
                    <div style="display:flex;gap:.75rem;align-items:flex-start">
                        <span style="flex:none;width:1.5rem;height:1.5rem;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;{{ $step['done'] ? 'background:#10b981;color:#fff' : 'background:rgba(128,128,128,.2)' }}">{{ $step['done'] ? '✓' : $loop->iteration }}</span>
                        <div>
                            <p style="font-weight:600">{{ $step['label'] }}</p>
                            <p class="fi-section-header-description">{{ $step['hint'] }}</p>
                        </div>
                    </div>

                    @unless ($step['done'])
                        <div>
                            <x-filament::button tag="a" :href="$step['url']" size="sm">{{ $step['cta'] }}</x-filament::button>
                        </div>
                    @endunless
                </li>
            @endforeach
        </ol>
    </x-filament::section>
</x-filament-widgets::widget>
