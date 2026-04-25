<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Loklify\Laravel\Tests\TestCase;

uses(TestCase::class);

it('fetches translations from the loklify api', function (): void {
    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response(
            ['auth.login' => 'Se connecter', 'auth.logout' => 'Se déconnecter'],
            200,
            ['ETag' => '"abc123"'],
        ),
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

it('stores the etag and translations in cache on a 200 response', function (): void {
    config(['loklify.cache_ttl' => 3600]);

    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response(
            ['nav.home' => 'Accueil'],
            200,
            ['ETag' => '"etag-v1"'],
        ),
    ]);

    app('translation.loader')->load('fr', '*', '*');

    expect(Cache::get('loklify.etag.fr'))->toBe('"etag-v1"')
        ->and(Cache::get('loklify.translations.fr'))->toBe(['nav.home' => 'Accueil'])
        ->and(Cache::has('loklify.fresh.fr'))->toBeTrue();
});

it('sends if-none-match header when etag is cached', function (): void {
    config(['loklify.cache_ttl' => 3600]);

    Cache::forever('loklify.etag.fr', '"etag-v1"');

    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response(
            ['nav.home' => 'Accueil'],
            200,
            ['ETag' => '"etag-v1"'],
        ),
    ]);

    app('translation.loader')->load('fr', '*', '*');

    Http::assertSent(fn ($request) => $request->hasHeader('If-None-Match', '"etag-v1"'));
});

it('uses cached translations on 304 and resets freshness', function (): void {
    config(['loklify.cache_ttl' => 3600]);

    Cache::forever('loklify.etag.fr', '"etag-v1"');
    Cache::forever('loklify.translations.fr', ['nav.home' => 'Accueil']);

    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response('', 304),
    ]);

    $translations = app('translation.loader')->load('fr', '*', '*');

    expect($translations)->toBe(['nav.home' => 'Accueil'])
        ->and(Cache::has('loklify.fresh.fr'))->toBeTrue();
});

it('updates cache when server returns 200 with a new etag', function (): void {
    config(['loklify.cache_ttl' => 3600]);

    Cache::forever('loklify.etag.fr', '"etag-v1"');
    Cache::forever('loklify.translations.fr', ['nav.home' => 'Accueil']);

    Http::fake([
        'api.loklify.test/api/projects/test-project-uuid/translations/fr' => Http::response(
            ['nav.home' => 'Accueil mis à jour'],
            200,
            ['ETag' => '"etag-v2"'],
        ),
    ]);

    $translations = app('translation.loader')->load('fr', '*', '*');

    expect($translations)->toBe(['nav.home' => 'Accueil mis à jour'])
        ->and(Cache::get('loklify.etag.fr'))->toBe('"etag-v2"');
});
