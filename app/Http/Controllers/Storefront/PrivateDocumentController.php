<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ProductDocument;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Uploaded paperwork (COA, MSDS, KYC certificates) never sits on a public
 * disk. Every download is authorised here and streamed from private storage.
 */
class PrivateDocumentController extends Controller
{
    public function product(Request $request, ProductDocument $document): StreamedResponse
    {
        $product = $document->product;

        abort_unless($product?->is_active && $product->vendor?->isApproved(), 404);

        // COA / MSDS style paperwork is gated behind a buyer account.
        abort_if($document->requires_login && ! $request->user(), 403, 'Sign in to download this document.');

        return $this->stream($document->file_path, $document->label ?? $document->type);
    }

    public function vendor(Request $request, VendorDocument $document): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $vendor = $document->vendor;

        $isStaff = $user->isAdmin()
            || $vendor->user_id === $user->id
            || $vendor->staff()->whereKey($user->id)->exists();

        // Public certificates are visible to signed-in buyers; GST/PAN never are.
        abort_unless($isStaff || ($document->is_public && $vendor->isApproved()), 403);

        return $this->stream($document->file_path, $document->label ?? $document->type);
    }

    private function stream(?string $path, string $name): StreamedResponse
    {
        abort_if(blank($path) || ! Storage::disk('local')->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($path, str($name)->slug().($extension ? ".{$extension}" : ''));
    }
}
