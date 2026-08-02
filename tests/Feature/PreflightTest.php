<?php

it('passes in a healthy local environment', function () {
    $this->artisan('vexporter:preflight')->assertSuccessful();
});

it('fails when production would run with debug on', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.debug' => true]);

    $this->artisan('vexporter:preflight')->assertFailed();
});

it('fails when production would queue jobs synchronously', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.debug' => false, 'app.url' => 'https://vexporter.com', 'queue.default' => 'sync']);

    $this->artisan('vexporter:preflight')->assertFailed();
});

it('fails when production would send mail to the log', function () {
    app()->detectEnvironment(fn () => 'production');
    config([
        'app.debug' => false,
        'app.url' => 'https://vexporter.com',
        'queue.default' => 'redis',
        'mail.default' => 'log',
    ]);

    $this->artisan('vexporter:preflight')->assertFailed();
});

it('treats placeholder gateway credentials as fatal in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config([
        'app.debug' => false,
        'app.url' => 'https://vexporter.com',
        'queue.default' => 'redis',
        'mail.default' => 'smtp',
        'services.razorpay.secret' => 'secret_dummy',
    ]);

    $this->artisan('vexporter:preflight')->assertFailed();
});

it('treats warnings as failures with --strict', function () {
    config(['services.razorpay.secret' => 'secret_dummy']);

    $this->artisan('vexporter:preflight', ['--strict' => true])->assertFailed();
});
