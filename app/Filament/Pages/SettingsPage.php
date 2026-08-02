<?php

namespace App\Filament\Pages;

use App\Support\SiteSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

/**
 * Shared plumbing for the content pages that edit `site_settings`: load the
 * groups this page owns, render them as one sticky-footer form, write them back
 * and drop the caches the storefront reads.
 */
abstract class SettingsPage extends Page
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /**
     * Form state key => settings group key, e.g. `hero` => `home.hero`.
     *
     * @return array<string, string>
     */
    abstract protected function settingGroups(): array;

    /** Caches to forget on save, on top of the settings cache itself. */
    protected function staleCacheKeys(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('content.manage') ?? false;
    }

    public function mount(): void
    {
        $settings = app(SiteSettings::class);

        $this->getSchema('form')?->fill(
            collect($this->settingGroups())
                ->map(fn (string $group): array => $settings->group($group))
                ->all()
        );
    }

    public function save(): void
    {
        $state = $this->getSchema('form')?->getState() ?? [];

        app(SiteSettings::class)->put(
            collect($this->settingGroups())
                ->filter(fn (string $group, string $key): bool => array_key_exists($key, $state))
                ->mapWithKeys(fn (string $group, string $key): array => [$group => $state[$key]])
                ->all()
        );

        foreach ($this->staleCacheKeys() as $key) {
            Cache::forget($key);
        }

        Notification::make()
            ->success()
            ->title('Saved')
            ->body('Changes are live on the storefront.')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                        Action::make('preview')
                            ->label('View storefront')
                            ->color('gray')
                            ->url(route('home'), shouldOpenInNewTab: true),
                    ])->sticky()->key('form-actions'),
                ]),
        ]);
    }
}
