<?php

use App\Support\Html;

it('keeps the formatting the editors produce', function () {
    expect(Html::sanitize('<p>Hello <strong>world</strong></p><ul><li>One</li></ul>'))
        ->toBe('<p>Hello <strong>world</strong></p><ul><li>One</li></ul>');
});

it('drops scripts, styles and embeds entirely', function (string $html) {
    expect(Html::sanitize($html))->not->toContain('alert');
})->with([
    '<script>alert(1)</script>',
    '<p>ok</p><script>alert(1)</script>',
    '<iframe src="javascript:alert(1)"></iframe>',
    '<style>body{background:url("javascript:alert(1)")}</style>',
]);

it('strips event handlers while keeping the text', function () {
    expect(Html::sanitize('<p onclick="alert(1)">Read me</p>'))->toBe('<p>Read me</p>');
});

it('strips an img tag but keeps surrounding copy', function () {
    expect(Html::sanitize('<p>Before<img src=x onerror=alert(1)>After</p>'))
        ->toBe('<p>BeforeAfter</p>');
});

it('removes unsafe link schemes and keeps safe ones', function () {
    expect(Html::sanitize('<a href="javascript:alert(1)">bad</a>'))->toBe('<a>bad</a>')
        ->and(Html::sanitize('<a href="/help">ok</a>'))->toBe('<a href="/help">ok</a>')
        ->and(Html::sanitize('<a href="https://example.com">ok</a>'))->toContain('href="https://example.com"');
});

it('adds noopener to links opening a new tab', function () {
    expect(Html::sanitize('<a href="https://example.com" target="_blank">x</a>'))
        ->toContain('rel="noopener noreferrer"');
});

it('unwraps disallowed containers rather than deleting their content', function () {
    expect(Html::sanitize('<div class="x">wrapped <em>text</em></div>'))->toBe('wrapped <em>text</em>');
});

it('passes null and empty values straight through', function () {
    expect(Html::sanitize(null))->toBeNull()
        ->and(Html::sanitize(''))->toBe('');
});

it('flattens markup to plain text for meta tags', function () {
    expect(Html::toText('<p>Hi <b>there</b></p><p>Second</p>'))->toBe('Hi there Second')
        ->and(Html::toText('<p>A very long sentence indeed</p>', 10))->toStartWith('A very lon')
        ->and(Html::toText('<p>A very long sentence indeed</p>', 10))->not->toContain('sentence');
});
