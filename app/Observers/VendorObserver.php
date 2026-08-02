<?php

namespace App\Observers;

use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;

class VendorObserver
{
    public function saved(Vendor $vendor): void
    {
        $this->clearCache();
    }

    public function deleted(Vendor $vendor): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget('homepage_top_vendors');
        Cache::forget('verified_vendors_count');
    }
}
