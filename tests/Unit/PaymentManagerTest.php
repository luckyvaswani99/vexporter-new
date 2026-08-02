<?php

use App\Payments\Gateways\BankTransferGateway;
use App\Payments\Gateways\RazorpayGateway;
use App\Payments\Gateways\StripeGateway;
use App\Payments\PaymentManager;

test('resolves default and dynamic payment gateway drivers correctly', function () {
    $manager = new PaymentManager;

    expect($manager->driver('razorpay'))->toBeInstanceOf(RazorpayGateway::class);
    expect($manager->driver('stripe'))->toBeInstanceOf(StripeGateway::class);
    expect($manager->driver('bank_transfer'))->toBeInstanceOf(BankTransferGateway::class);

    expect($manager->resolveForCurrency('INR'))->toBeInstanceOf(RazorpayGateway::class);
    expect($manager->resolveForCurrency('USD'))->toBeInstanceOf(StripeGateway::class);
    expect($manager->resolveForCurrency('EUR'))->toBeInstanceOf(StripeGateway::class);
});
