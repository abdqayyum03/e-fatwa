<?php

namespace App\Scrapers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client shared by the adapters and the PDF downloader.
 * Certificate verification comes from the app-wide bundle (App\Support\CaBundle).
 */
final class ScraperClient
{
    public static function make(): PendingRequest
    {
        return Http::withHeaders(['User-Agent' => 'e-fatwa-scraper/1.0 (academic project)']);
    }
}
