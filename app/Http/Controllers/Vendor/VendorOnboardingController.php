<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Vendors\SubmitVendorApplication;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorApplicationRequest;
use App\Models\Vertical;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorOnboardingController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if ($request->user()->vendor()->exists()) {
            return redirect()->route('vendor.onboarding.status');
        }

        return view('vendor.onboarding', [
            'verticals' => Vertical::with('categories')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(VendorApplicationRequest $request, SubmitVendorApplication $action): RedirectResponse
    {
        $action->handle(
            $request->user(),
            $request->validated(),
            $this->documents($request),
        );

        return redirect()
            ->route('vendor.onboarding.status')
            ->with('status', 'Application submitted — our team reviews vendors within two business days.');
    }

    public function status(Request $request): RedirectResponse|View
    {
        $vendor = $request->user()->vendor()->with(['documents', 'kycLogs'])->first();

        if (! $vendor) {
            return redirect()->route('vendor.onboarding.create');
        }

        return view('vendor.onboarding-status', ['vendor' => $vendor]);
    }

    /**
     * Normalises the repeatable certification rows into the shape the action
     * expects, dropping any empty rows the buyer left behind.
     *
     * @return array<int, array<string, mixed>>
     */
    private function documents(Request $request): array
    {
        return collect($request->input('documents', []))
            ->map(fn (array $row, int $index) => [
                'label' => $row['label'] ?? null,
                'number' => $row['number'] ?? null,
                'expires_at' => $row['expires_at'] ?? null,
                'file' => $request->file("documents.{$index}.file"),
            ])
            ->filter(fn (array $row) => filled($row['label']))
            ->values()
            ->all();
    }
}
