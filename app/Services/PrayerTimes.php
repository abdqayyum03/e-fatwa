<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Today's prayer times from JAKIM e-Solat, cached per zone until midnight
 * so the government API is called at most once a day per zone.
 */
class PrayerTimes
{
    private const API = 'https://www.e-solat.gov.my/index.php';

    /** e-Solat keys => labels shown on the site, in order. */
    public const PRAYERS = [
        'imsak' => 'Imsak',
        'fajr' => 'Subuh',
        'syuruk' => 'Syuruk',
        'dhuhr' => 'Zohor',
        'asr' => 'Asar',
        'maghrib' => 'Maghrib',
        'isha' => 'Isyak',
    ];

    public const DEFAULT_ZONE = 'WLY01';

    /** @return array<string, array<string, string>> state => [code => areas] */
    public static function zones(): array
    {
        return config('prayer_zones');
    }

    public static function isValidZone(string $zone): bool
    {
        return collect(self::zones())->contains(fn ($areas) => array_key_exists($zone, $areas));
    }

    public static function zoneLabel(string $zone): ?string
    {
        foreach (self::zones() as $state => $areas) {
            if (isset($areas[$zone])) {
                return "{$state}: {$areas[$zone]}";
            }
        }

        return null;
    }

    /**
     * @return array{zone:string, label:?string, date:string, hijri:?string, times:array<string,string>}|null
     */
    public function today(string $zone): ?array
    {
        $now = Carbon::now('Asia/Kuala_Lumpur');
        $key = "esolat:{$zone}:{$now->toDateString()}";

        $data = Cache::get($key);

        if ($data === null) {
            $data = $this->fetch($zone);

            if ($data) {
                // Keep until the end of the day in Malaysia.
                Cache::put($key, $data, $now->copy()->endOfDay());
            }
        }

        return $data;
    }

    private function fetch(string $zone): ?array
    {
        try {
            $response = Http::timeout(15)
                ->retry(1, 1000, throw: false)
                ->get(self::API, ['r' => 'esolatApi/takwimsolat', 'period' => 'today', 'zone' => $zone]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        $day = $response->json('prayerTime.0');

        if (! $response->successful() || ! $day) {
            return null;
        }

        $times = [];
        foreach (array_keys(self::PRAYERS) as $key) {
            if (! empty($day[$key])) {
                $times[$key] = substr($day[$key], 0, 5); // "13:02:00" -> "13:02"
            }
        }

        return [
            'zone' => $zone,
            'label' => self::zoneLabel($zone),
            'date' => $day['date'] ?? null,
            'hijri' => $day['hijri'] ?? null,
            'times' => $times,
        ];
    }
}
