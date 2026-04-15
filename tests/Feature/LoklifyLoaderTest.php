<?php

use Illuminate\Support\Facades\Http;
use Loklify\Laravel\Tests\TestCase;

uses(TestCase::class);

it('fetches translations from the loklify api', function (): void {
    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response([
            'auth.login' => 'Se connecter',
            'auth.logout' => 'Se déconnecter',
        ]),
    ]);

    app()->setLocale('fr');

    expect(__('auth.login'))->toBe('Se connecter')
        ->and(__('auth.logout'))->toBe('Se déconnecter');
});

it('returns the key when the translation is missing', function (): void {
    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response([]),
    ]);

    app()->setLocale('fr');

    expect(__('auth.login'))->toBe('auth.login');
});

it('does not call the api for file-based group translations', function (): void {
    Http::fake();

    app('translation.loader')->load('en', 'validation', null);

    Http::assertNothingSent();
});
