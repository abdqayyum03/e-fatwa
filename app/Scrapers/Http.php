<?php

namespace App\Scrapers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\PendingRequest;

/** Polite HTTP helpers shared by the adapters. */
trait Http
{
    /** Pause between requests so we never hammer a government website. */
    protected int $delayMs = 1000;

    protected function client(): PendingRequest
    {
        return ScraperClient::make()->timeout(60)->retry(2, 3000, throw: false);
    }

    protected function getHtml(string $url): DOMXPath
    {
        usleep($this->delayMs * 1000);

        $html = $this->client()->get($url)->throw()->body();

        $dom = new DOMDocument;
        // The meta tag makes DOMDocument read the page as UTF-8 (Malay quotes, etc.).
        @$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$html);

        return new DOMXPath($dom);
    }

    protected function getJson(string $url, array $query = []): array
    {
        usleep($this->delayMs * 1000);

        $response = $this->client()->get($url, $query)->throw();

        return ['data' => $response->json(), 'headers' => $response->headers()];
    }

    protected static function clean(?string $text): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $text === '' ? null : $text;
    }

    protected static function absolute(string $base, string $href): string
    {
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }

        $parts = parse_url($base);

        return $parts['scheme'].'://'.$parts['host'].'/'.ltrim($href, '/');
    }
}
