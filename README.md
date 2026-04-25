# Loklify for Laravel

[![Latest Version](https://img.shields.io/packagist/v/loklify/laravel.svg)](https://packagist.org/packages/loklify/laravel)
[![License](https://img.shields.io/packagist/l/loklify/laravel.svg)](https://packagist.org/packages/loklify/laravel)

The official Laravel package for [Loklify](https://loklify.com) — the simple i18n platform for small teams.

Loklify replaces your local JSON translation files with a hosted API. Update translations on [loklify.com](https://loklify.com) and your Laravel app picks them up at runtime — no rebuild, no redeploy.

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Installation

```bash
composer require loklify/laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag=loklify-config
```

## Configuration

Add these variables to your `.env` file:

```env
LOKLIFY_PROJECT_ID=your-project-uuid
LOKLIFY_CACHE_TTL=3600
```

| Variable | Description | Default |
|---|---|---|
| `LOKLIFY_PROJECT_ID` | Your project UUID (found in your [Loklify](https://loklify.com) dashboard) | — |
| `LOKLIFY_CACHE_TTL` | Cache duration in seconds. Set to `0` to disable caching. | `3600` |

> The translation endpoint is **public** — no API token is required.

### Config file

After publishing, the config is available at `config/loklify.php`:

```php
return [
    'url'        => env('LOKLIFY_URL', 'https://api.loklify.com'),
    'project_id' => env('LOKLIFY_PROJECT_ID'),
    'token'      => env('LOKLIFY_TOKEN'),
    'cache_ttl'  => env('LOKLIFY_CACHE_TTL', 3600),
];
```

## Usage

Once installed, Laravel's built-in translation system (`__()`, `@lang`, `trans()`) works with Loklify out of the box.

```php
// In a controller
return view('welcome', [
    'greeting' => __('home.welcome'),
]);
```

```blade
{{-- In Blade --}}
<h1>{{ __('home.welcome') }}</h1>
<p>@lang('home.subtitle')</p>
```

Translations are fetched from your [Loklify](https://loklify.com) project and cached locally for the configured TTL.

### How it works

The package registers a custom `LoklifyLoader` that wraps Laravel's default file-based translation loader:

1. **JSON translations** (group `*`, namespace `*`) are loaded from the Loklify API
2. **All other translations** (PHP files, vendor namespaces) fall through to the default Laravel loader
3. **ETag support** — the loader sends `If-None-Match` headers. When translations haven't changed, the API returns `304 Not Modified` and the local cache is reused without downloading data
4. **Caching** — translations are cached locally. A freshness key prevents hitting the API on every request. After the TTL expires, the next request revalidates with the server

### Cache flow

```
Request → Is fresh? (TTL not expired)
  ├── Yes → Return cached translations (no HTTP call)
  └── No → Send request with ETag
        ├── 304 Not Modified → Refresh TTL, return cached
        └── 200 OK → Store new translations + ETag, refresh TTL
```

## Managing translations

Create and manage your translations on [loklify.com](https://loklify.com):

1. Create a project and add your languages
2. Add translation keys (e.g. `home.welcome`, `auth.login`)
3. Fill in translations — changes are live instantly
4. Your Laravel app fetches them at runtime

## Clearing the cache

To force-refresh translations:

```bash
php artisan cache:forget loklify.fresh.fr
php artisan cache:forget loklify.fresh.en
```

Or clear all cache:

```bash
php artisan cache:clear
```

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE](LICENSE) for details.

## Links

- [Loklify Platform](https://loklify.com)
- [Documentation](https://loklify.com)
- [GitHub](https://github.com/loklify/laravel)
