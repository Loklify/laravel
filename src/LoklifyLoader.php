<?php

namespace Loklify\Laravel;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LoklifyLoader implements Loader
{
    public function __construct(private readonly Loader $fileLoader) {}

    public function load($locale, $group, $namespace = null): array
    {
        if ($group === '*' && $namespace === '*') {
            return $this->loadFromLoklify($locale);
        }

        return $this->fileLoader->load($locale, $group, $namespace);
    }

    /**
     * @return array<string, string>
     */
    private function loadFromLoklify(string $locale): array
    {
        $ttl = (int) config('loklify.cache_ttl', 3600);

        $fetch = fn (): array => Http::withToken(config('loklify.token'))
            ->get(rtrim((string) config('loklify.url'), '/').'/api/projects/'.config('loklify.project_id')."/translations/{$locale}")
            ->json() ?? [];

        if ($ttl === 0) {
            return $fetch();
        }

        return Cache::remember("loklify.translations.{$locale}", $ttl, $fetch);
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
