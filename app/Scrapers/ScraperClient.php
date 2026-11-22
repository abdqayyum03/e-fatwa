<?php

namespace App\Scrapers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** HTTP client shared by the adapters and the PDF downloader. */
final class ScraperClient
{
    public static function make(): PendingRequest
    {
        return Http::withHeaders(['User-Agent' => 'e-fatwa-scraper/1.0 (academic project)'])
            ->withOptions(['verify' => self::caBundle()]);
    }

    /**
     * Some mufti sites send an incomplete certificate chain (browsers repair this
     * silently, PHP does not). We trust the normal CA list plus the missing
     * intermediates kept in resources/certs, combined into one cached file.
     */
    private static function caBundle(): string|bool
    {
        $system = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
        $extras = glob(resource_path('certs/*.pem')) ?: [];

        if (! $system || ! is_file($system)) {
            return true; // fall back to PHP's default verification
        }

        $bundle = storage_path('app/scraper-ca-bundle.pem');
        $newest = max(array_map('filemtime', [$system, ...$extras]));

        if (! is_file($bundle) || filemtime($bundle) < $newest) {
            $pem = file_get_contents($system);
            foreach ($extras as $file) {
                $pem .= "\n".file_get_contents($file);
            }
            file_put_contents($bundle, $pem);
        }

        return $bundle;
    }
}
