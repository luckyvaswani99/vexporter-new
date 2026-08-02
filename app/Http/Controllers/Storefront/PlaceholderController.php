<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Renders the "under construction" screen for routes whose real
 * implementation lands in a later phase. Keeps every navigation link in the
 * storefront valid while the build is in progress.
 */
class PlaceholderController extends Controller
{
    public function __invoke(string $heading = 'Coming soon', ?string $note = null): View
    {
        return view('storefront.placeholder', [
            'heading' => $heading,
            'note' => $note,
        ]);
    }
}
