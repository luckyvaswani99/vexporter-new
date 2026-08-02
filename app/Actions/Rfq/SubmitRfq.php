<?php

namespace App\Actions\Rfq;

use App\Models\Product;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\RfqInvitation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SubmitRfq
{
    /**
     * Creates the request and invites the vendors most likely to quote it —
     * the listed vendor first, then others selling in the same category.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $buyer, array $data): Rfq
    {
        $product = isset($data['product_id']) ? Product::find($data['product_id']) : null;

        $rfq = DB::transaction(function () use ($buyer, $data, $product): Rfq {
            $rfq = Rfq::create([
                'reference' => $this->reference(),
                'buyer_id' => $buyer->id,
                'status' => Rfq::STATUS_OPEN,
                'target_type' => $product ? 'product' : 'open',
                'product_id' => $product?->id,
                'category_id' => $product?->category_id,
                'vertical_id' => $product?->vertical_id ?? ($data['vertical_id'] ?? null),
                'title' => $data['title'],
                'description' => $data['description'],
                'qty' => $data['qty'],
                'unit' => $data['unit'],
                'target_price' => isset($data['target_price']) ? (int) round((float) $data['target_price'] * 100) : null,
                'currency' => config('vexporter.default_currency'),
                'destination_country' => strtoupper($data['destination_country']),
                'incoterm' => $data['incoterm'],
                'delivery_by' => $data['delivery_by'] ?? null,
                'expires_at' => now()->addDays(14),
            ]);

            $vendors = $this->matchingVendors($rfq, $product);

            $rfq->vendors()->attach(
                $vendors->mapWithKeys(fn (Vendor $vendor) => [
                    $vendor->id => ['invited_at' => now(), 'status' => 'invited'],
                ])->all(),
            );

            return $rfq;
        });

        $invited = $rfq->vendors()->with('owner')->get();

        Notification::send(
            $invited->map(fn (Vendor $vendor) => $vendor->owner)->filter(),
            new RfqInvitation($rfq),
        );

        return $rfq;
    }

    /** @return Collection<int, Vendor> */
    private function matchingVendors(Rfq $rfq, ?Product $product)
    {
        $query = Vendor::approved();

        if ($product) {
            $query->where(fn ($inner) => $inner
                ->whereKey($product->vendor_id)
                ->orWhereHas('products', fn ($products) => $products->where('category_id', $product->category_id)));
        } elseif ($rfq->vertical_id) {
            $query->whereHas('products', fn ($products) => $products->where('vertical_id', $rfq->vertical_id));
        }

        return $query->orderByDesc('rating_cache')->take(8)->get();
    }

    private function reference(): string
    {
        $year = now()->format('Y');
        $sequence = Rfq::whereYear('created_at', $year)->count() + 1;

        return 'RFQ-'.$year.'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
