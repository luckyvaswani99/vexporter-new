<?php

use App\Support\Money;

it('formats minor units the way the catalogue quotes them', function (?int $minor, string $currency, ?string $expected) {
    expect(Money::format($minor, $currency))->toBe($expected);
})->with([
    'cents below one hundred' => [850, 'USD', '$8.50'],
    'whole dollars below one hundred' => [4500, 'USD', '$45.00'],
    'exactly one hundred' => [10000, 'USD', '$100'],
    'thousands' => [125000, 'USD', '$1,250'],
    'six figures' => [14500000, 'USD', '$145,000'],
    'zero' => [0, 'USD', '$0.00'],
    'rupees' => [99900, 'INR', '₹999'],
    'euro' => [5000, 'EUR', '€50.00'],
    'unknown currency falls back to the code' => [5000, 'XYZ', 'XYZ 50.00'],
    'null stays null' => [null, 'USD', null],
]);

it('converts major units to minor without float drift', function () {
    expect(Money::toMinor(8.5))->toBe(850)
        ->and(Money::toMinor('19.99'))->toBe(1999)
        ->and(Money::toMinor(0.1 + 0.2))->toBe(30)
        ->and(Money::toMinor(145000))->toBe(14500000);
});

it('round-trips a value through minor units', function () {
    $minor = Money::toMinor('1250.00');

    expect($minor)->toBe(125000)
        ->and(Money::format($minor))->toBe('$1,250');
});
