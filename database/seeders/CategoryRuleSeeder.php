<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryRule;
use Illuminate\Database\Seeder;

/**
 * Starter keyword rules for categorising fatwas the mufti sites publish without
 * a category. Admins edit them at /admin/kategori. Lower priority wins, so a
 * "zakat perniagaan" fatwa goes to Zakat (10) rather than Muamalat (60); two
 * rules of different categories with the same priority leave the fatwa alone.
 * Only adds rules for categories that have none yet, so re-running is safe.
 */
class CategoryRuleSeeder extends Seeder
{
    private const RULES = [
        // category => [priority, keywords]
        'Zakat' => [10, 'zakat, fitrah, nisab, haul, asnaf, amil'],
        'Wakaf' => [10, 'wakaf'],
        'Faraid' => [20, 'faraid, pusaka, harta pusaka, wasiat, hibah, ahli waris'],
        'Masjid' => [20, 'masjid, surau, musolla'],
        'Perubatan' => [30, 'vaksin, vaksinasi, pembedahan, perubatan, ubat, rawatan, organ, pemindahan organ, darah, plasma, janin, pengguguran, covid-19, covid, hospital, bayi tabung uji, ivf'],
        'Munakahat' => [30, 'nikah, pernikahan, perkahwinan, berkahwin, kahwin, talak, cerai, perceraian, poligami, mahar, iddah, rujuk'],
        'Akidah' => [40, 'akidah, aqidah, ajaran, sesat, menyeleweng, penyelewengan, fahaman, syiah, qadiani, ahmadiah, ahmadiyah, bahai, baha’i, tariqat, tarekat, liberalisme, pluralisme, wahabi, wahabiah, murtad, khurafat, azimat, kalimah allah, ahli sunnah, ahli sunah'],
        'Ibadat' => [50, 'solat, sembahyang, puasa, berpuasa, ramadan, ramadhan, haji, umrah, korban, qurban, akikah, aqiqah, azan, jumaat, zikir, doa, jenazah, kiblat, anak bulan, rukyah, hilal, wuduk, tayammum, iktikaf, tarawih'],
        'Makan dan Minum' => [50, 'makanan, minuman, sembelih, sembelihan, penyembelihan, arak, alkohol, gelatin'],
        'Muamalat' => [60, 'riba, pelaburan, insurans, insuran, takaful, saham, perbankan, pinjaman, jual beli, kewangan, perniagaan, mata wang kripto, bitcoin, skim, hutang, sukuk, cagaran, gadaian, ar-rahnu'],
    ];

    public function run(): void
    {
        foreach (self::RULES as $name => [$priority, $keywords]) {
            $category = Category::firstOrCreate(['category_name' => $name]);

            if (! $category->rules()->exists()) {
                CategoryRule::create(['category_id' => $category->id, 'keywords' => $keywords, 'priority' => $priority]);
            }
        }
    }
}
