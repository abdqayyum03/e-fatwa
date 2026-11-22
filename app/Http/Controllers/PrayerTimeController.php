<?php

namespace App\Http\Controllers;

use App\Services\PrayerTimes;
use Illuminate\Http\Request;

class PrayerTimeController extends Controller
{
    /** JSON for the prayer-time widget: /waktu-solat?zone=WLY01 */
    public function __invoke(Request $request, PrayerTimes $prayerTimes)
    {
        $zone = strtoupper((string) $request->query('zone', PrayerTimes::DEFAULT_ZONE));

        if (! PrayerTimes::isValidZone($zone)) {
            return response()->json(['message' => 'Zon tidak sah.'], 422);
        }

        $data = $prayerTimes->today($zone);

        if (! $data) {
            return response()->json(['message' => 'Waktu solat tidak dapat diperoleh daripada e-Solat buat masa ini.'], 503);
        }

        return response()->json($data + ['labels' => PrayerTimes::PRAYERS]);
    }
}
