<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Vertical;
use App\Support\Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = Vendor::approved()
            ->with(['documents', 'products.category'])
            ->when($request->query('q'), fn (Builder $query, string $term) => $query->where('name', 'like', "%{$term}%"))
            ->when($request->query('country'), fn (Builder $query, string $country) => $query->where('country_code', $country))
            ->when($request->query('vertical'), fn (Builder $query, string $vertical) => $query->whereHas(
                'products.vertical',
                fn (Builder $inner) => $inner->where('slug', $vertical),
            ))
            ->when($request->query('certification'), fn (Builder $query, string $certification) => $query->whereHas(
                'documents',
                fn (Builder $inner) => $inner->where('label', $certification)->where('status', 'verified'),
            ))
            ->orderByDesc('rating_cache')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.vendors.index', [
            'vendors' => $vendors,
            'verticals' => Vertical::orderBy('sort_order')->get(),
            'countries' => Vendor::approved()->distinct()->pluck('country_code')->mapWithKeys(
                fn (string $code) => [$code => Countries::name($code)],
            ),
            'certifications' => array_keys(config('vexporter.certifications')),
        ]);
    }

    public function show(Request $request, Vendor $vendor): View
    {
        abort_unless($vendor->isApproved(), 404);

        $vendor->load('documents');

        return view('storefront.vendors.show', [
            'vendor' => $vendor,
            'products' => Product::visible()
                ->where('vendor_id', $vendor->id)
                ->with(['vendor', 'category', 'certificates'])
                ->when($request->query('q'), fn (Builder $query, string $term) => $query->where('name', 'like', "%{$term}%"))
                ->orderByDesc('is_featured')
                ->orderByDesc('rating_cache')
                ->paginate(12)
                ->withQueryString(),
            'categories' => Product::visible()
                ->where('vendor_id', $vendor->id)
                ->with('category')
                ->get()
                ->pluck('category')
                ->filter()
                ->unique('id')
                ->values(),
        ]);
    }
}
