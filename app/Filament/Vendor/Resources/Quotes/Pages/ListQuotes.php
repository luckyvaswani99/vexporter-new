<?php

namespace App\Filament\Vendor\Resources\Quotes\Pages;

use App\Filament\Vendor\Resources\Quotes\QuoteResource;
use Filament\Resources\Pages\ListRecords;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;
}
