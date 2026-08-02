<?php

namespace App\Filament\Pages;

use App\Support\PaymentMethods;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * What buyers may pay with, and the wire details printed on the payment page
 * and proforma invoice. API keys are not editable here — they belong in the
 * environment, so this page reports their state instead.
 */
class ManagePaymentMethods extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Payment methods';

    protected static ?string $navigationLabel = 'Payment methods';

    /** Money settings sit behind their own permission, not the content one. */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    protected function settingGroups(): array
    {
        return ['methods' => 'payments.methods', 'bank' => 'payments.bank'];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Methods offered at checkout')
                    ->description('Drag to reorder — the first enabled method is pre-selected unless the order currency suggests another.')
                    ->schema([
                        Repeater::make('methods')
                            ->hiddenLabel()
                            ->schema([
                                Hidden::make('gateway'),

                                Toggle::make('enabled')->label('Offer this method')->live(),

                                Text::make(fn (Get $get): string => $this->credentialNote((string) $get('gateway')))
                                    ->visible(fn (Get $get): bool => (bool) $get('enabled')),

                                TextInput::make('label')
                                    ->label('Name shown to buyers')
                                    ->required()
                                    ->maxLength(60),

                                TextInput::make('blurb')
                                    ->label('One-line summary')
                                    ->maxLength(120),

                                TextInput::make('icon')
                                    ->label('Icon')
                                    ->helperText('Font Awesome class, e.g. fa-solid fa-credit-card.')
                                    ->maxLength(60),

                                Textarea::make('panel_note')
                                    ->label('Reassurance text')
                                    ->rows(2)
                                    ->maxLength(300),
                            ])
                            ->itemLabel(fn (array $state): string => PaymentMethods::GATEWAYS[$state['gateway'] ?? ''] ?? 'Unknown')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable()
                            ->collapsible()
                            ->collapsed(),
                    ]),

                Section::make('Wire transfer details')
                    ->description('Printed on the payment page and the proforma invoice. Leave blank and buyers are told the details will be emailed with their invoice.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bank.beneficiary')->label('Beneficiary name')->maxLength(120),
                        TextInput::make('bank.bank_name')->label('Bank')->maxLength(120),
                        TextInput::make('bank.account_number')->label('Account number')->maxLength(64),
                        TextInput::make('bank.swift')->label('SWIFT / BIC')->maxLength(32),
                        TextInput::make('bank.ifsc')->label('IFSC')->maxLength(32),
                        TextInput::make('bank.branch')->label('Branch')->maxLength(160),
                        Textarea::make('bank.notes')
                            ->label('Extra instructions')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private function credentialNote(string $gateway): string
    {
        if (! in_array($gateway, PaymentMethods::CREDENTIALLED, true)) {
            return 'No API keys needed — settlement is manual.';
        }

        return PaymentMethods::hasLiveCredentials($gateway)
            ? 'API keys are configured.'
            : "Still on placeholder keys — set {$this->envKey($gateway)} in the environment before taking real payments.";
    }

    private function envKey(string $gateway): string
    {
        return strtoupper($gateway).'_KEY / '.strtoupper($gateway).'_SECRET';
    }
}
