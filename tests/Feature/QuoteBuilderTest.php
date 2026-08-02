<?php

use App\Actions\Rfq\AcceptQuote;
use App\Actions\Rfq\SubmitQuote;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\QuoteAccepted;
use App\Notifications\QuoteReceived;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

function openRfq(User $buyer, Vendor $vendor, ?Product $product = null): Rfq
{
    $product ??= Product::factory()->create(['vendor_id' => $vendor->id]);

    $rfq = Rfq::create([
        'reference' => 'RFQ-2026-'.str_pad((string) (Rfq::count() + 1), 6, '0', STR_PAD_LEFT),
        'buyer_id' => $buyer->id,
        'status' => Rfq::STATUS_OPEN,
        'target_type' => 'product',
        'product_id' => $product->id,
        'category_id' => $product->category_id,
        'vertical_id' => $product->vertical_id,
        'title' => '5 tons of API',
        'description' => 'WHO-GMP material with COA.',
        'qty' => 5,
        'unit' => 'ton',
        'currency' => 'USD',
        'destination_country' => 'AE',
        'incoterm' => 'CIF',
        'expires_at' => now()->addDays(14),
    ]);

    $rfq->vendors()->attach($vendor->id, ['invited_at' => now(), 'status' => 'invited']);

    return $rfq;
}

function quotePayload(array $overrides = []): array
{
    return array_merge([
        'currency' => 'USD',
        'incoterm' => 'CIF',
        'shipping' => 50_000,
        'tax' => 0,
        'lead_time_days' => 21,
        'validity_until' => now()->addDays(10)->toDateString(),
        'payment_terms' => '30% advance, 70% against BL',
        'items' => [
            ['description' => 'Paracetamol API BP', 'qty' => 5, 'unit' => 'ton', 'unit_price' => 1_200_000],
        ],
    ], $overrides);
}

it('lets an invited vendor send a quote', function () {
    Notification::fake();

    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rfq = openRfq($buyer, $vendor);

    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload());

    expect($quote->status)->toBe(Quote::STATUS_SENT)
        ->and($quote->subtotal)->toBe(6_000_000)
        ->and($quote->total)->toBe(6_050_000)
        ->and($quote->items)->toHaveCount(1)
        ->and($rfq->fresh()->status)->toBe(Rfq::STATUS_QUOTED)
        ->and($rfq->vendors()->first()->pivot->status)->toBe('quoted');

    Notification::assertSentTo($buyer, QuoteReceived::class);
});

it('drops empty line items from a quote', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rfq = openRfq($buyer, $vendor);

    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload([
        'items' => [
            ['description' => 'Real line', 'qty' => 2, 'unit' => 'ton', 'unit_price' => 100_000],
            ['description' => '', 'qty' => 0, 'unit' => 'ton', 'unit_price' => 0],
        ],
    ]));

    expect($quote->items)->toHaveCount(1);
});

it('turns an accepted quote into a confirmed order', function () {
    Notification::fake();

    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create(['commission_percent' => 5]);
    $rfq = openRfq($buyer, $vendor);

    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload());

    $order = app(AcceptQuote::class)->handle($quote->fresh());

    expect($order->source)->toBe('quote')
        ->and($order->quote_id)->toBe($quote->id)
        ->and($order->status)->toBe(Order::STATUS_CONFIRMED)
        ->and($order->grand_total)->toBe($quote->total)
        ->and($order->subOrders)->toHaveCount(1);

    $subOrder = $order->subOrders->first();

    // 5% of the 6,000,000 subtotal.
    expect($subOrder->commission_amount)->toBe(300_000)
        ->and($subOrder->vendor_payout_amount)->toBe($quote->total - 300_000)
        ->and($subOrder->items)->toHaveCount(1)
        ->and($quote->fresh()->status)->toBe(Quote::STATUS_ACCEPTED)
        ->and($rfq->fresh()->status)->toBe(Rfq::STATUS_CONVERTED);

    Notification::assertSentTo($vendor->owner, QuoteAccepted::class);
});

it('closes competing quotes when one is accepted', function () {
    $buyer = User::factory()->create();
    $vendorA = Vendor::factory()->create();
    $vendorB = Vendor::factory()->create();

    $rfq = openRfq($buyer, $vendorA);
    $rfq->vendors()->attach($vendorB->id, ['invited_at' => now(), 'status' => 'invited']);

    $quoteA = app(SubmitQuote::class)->handle($rfq, $vendorA, quotePayload());
    $quoteB = app(SubmitQuote::class)->handle($rfq, $vendorB, quotePayload());

    app(AcceptQuote::class)->handle($quoteA->fresh());

    expect($quoteB->fresh()->status)->toBe(Quote::STATUS_REJECTED);
});

it('refuses to accept an expired quote', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rfq = openRfq($buyer, $vendor);

    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload([
        'validity_until' => now()->subDay()->toDateString(),
    ]));

    expect(fn () => app(AcceptQuote::class)->handle($quote->fresh()))
        ->toThrow(ValidationException::class);

    expect(Order::count())->toBe(0);
});

it('lets the buyer accept a quote from their RFQ page', function () {
    Notification::fake();

    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rfq = openRfq($buyer, $vendor);
    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload());

    $this->actingAs($buyer)
        ->get(route('account.rfqs.show', $rfq))
        ->assertOk()
        ->assertSee('Accept this quote')
        ->assertSee($vendor->name);

    $this->actingAs($buyer)
        ->post(route('account.quotes.accept', [$rfq, $quote]))
        ->assertRedirect(route('account.orders.show', Order::firstOrFail()));

    expect($quote->fresh()->status)->toBe(Quote::STATUS_ACCEPTED);
});

it('stops another buyer from accepting a quote', function () {
    $buyer = User::factory()->create();
    $stranger = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rfq = openRfq($buyer, $vendor);
    $quote = app(SubmitQuote::class)->handle($rfq, $vendor, quotePayload());

    $this->actingAs($stranger)
        ->post(route('account.quotes.accept', [$rfq, $quote]))
        ->assertForbidden();

    expect(Order::count())->toBe(0);
});

it('shows a vendor only the RFQs it was invited to', function () {
    $buyer = User::factory()->create();

    $mine = Vendor::factory()->create();
    $mine->staff()->attach($mine->user_id, ['role' => 'owner']);
    $mine->owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    $other = Vendor::factory()->create();

    $invited = openRfq($buyer, $mine);
    $notInvited = openRfq($buyer, $other);
    $notInvited->update(['title' => 'Someone elses request']);

    $this->actingAs($mine->owner)
        ->get("/vendor/store/{$mine->slug}/rfqs")
        ->assertOk()
        ->assertSee($invited->title)
        ->assertDontSee('Someone elses request');
});
