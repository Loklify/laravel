<?php

namespace Loklify\Laravel;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

readonly class LoklifyLoader implements Loader
{
    public function __construct(private Loader $fileLoader) {}

    /**
     * @throws ConnectionException
     */
    public function load($locale, $group, $namespace = null): array
    {
        if ($group === '*' && $namespace === '*') {
            return $this->loadFromLoklify($locale);
        }

        return $this->fileLoader->load($locale, $group, $namespace);
    }

    /**
     * @return array<string, string>
     * @throws ConnectionException
     */
    private function loadFromLoklify(string $locale): array
    {
        $ttl = (int) config('loklify.cache_ttl', 3600);
        $translationsKey = "loklify.translations.{$locale}";
        $freshnessKey = "loklify.fresh.{$locale}";
        $etagKey = "loklify.etag.{$locale}";

        if ($ttl > 0 && Cache::has($freshnessKey)) {
            return Cache::get($translationsKey, []);
        }

        $etag = $ttl > 0 ? Cache::get($etagKey) : null;

        $response = Http::withToken(config('loklify.token'))
            ->when($etag, fn ($http) => $http->withHeaders(['If-None-Match' => $etag]))
            ->get(rtrim((string) config('loklify.url'), '/').'/api/projects/'.config('loklify.project_id')."/translations/{$locale}");

        if ($response->status() === 304) {
            if ($ttl > 0) {
                Cache::put($freshnessKey, true, $ttl);
            }

            return Cache::get($translationsKey, []);
        }

        $translations = $response->json() ?? [];

        if (empty($translations)) {
            return [];
        }

        if ($ttl > 0) {
            Cache::forever($translationsKey, $translations);
            Cache::put($freshnessKey, true, $ttl);

            $responseEtag = $response->header('ETag');
            if ($responseEtag) {
                Cache::forever($etagKey, $responseEtag);
            }
        }

        return $translations;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->fileLoader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->fileLoader->addJsonPath($path);
    }

    public function namespaces(): array
    {
        return $this->fileLoader->namespaces();
    }
}
