<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;

/**
 * A price field people can type in normal units (12.50) while the database
 * keeps integer minor units (1250).
 */
class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->formatStateUsing(fn ($state) => $state === null || $state === '' ? null : round(((int) $state) / 100, 2))
            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : (int) round(((float) $state) * 100));
    }
}
