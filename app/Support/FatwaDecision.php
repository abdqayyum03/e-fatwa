<?php

namespace App\Support;

/**
 * Picks the decision ("keputusan") out of a fatwa's full text, so the page can
 * show it at the top. Rule-based: the mufti departments word their decisions
 * in a few set ways, e.g.
 *
 *   "... telah bersetuju memutuskan bahawa: <decision>"        (most states)
 *   "... membuat keputusan sebagaimana berikut: <decision>"    (Johor)
 *   "Fatwanya adalah seperti berikut: <decision>"              (Pulau Pinang)
 *   "KESIMPULAN" / "KEPUTUSAN" heading                         (Kedah irsyad)
 *   "JADUAL  <TITLE>  <decision>"                               (gazettes)
 *   "Bil. 6/71 TAHUN 2024  <TITLE>  <decision>  -Tamat-"       (Perlis)
 *
 * The passage runs until a signature or closing line ("Bertarikh", "-Tamat-",
 * "Wallahu a'lam"...). Returns null when no marker is found, so the page simply
 * shows no decision box rather than a wrong one.
 */
class FatwaDecision
{
    private const MAX_CHARS = 1500;

    private const MIN_CHARS = 40;

    /** Tried in order; the text after the first match is the decision. */
    private const START_MARKERS = [
        // An explicit heading at the start of a line: "KESIMPULAN", "Keputusan:".
        '/\n[ \t]*(?:KESIMPULAN|RUMUSAN|KEPUTUSAN(?: FATWA)?)[ \t]*(?:[:;][ \t]*|\n)/iu',
        // "... (bersetuju) memutuskan (sebagaimana/seperti berikut | ... bahawa) :"
        '/memutuskan(?:\s+(?:untuk\s+menerima[^:]{0,200}?)?(?:sebagaimana|seperti)\s+(?:yang\s+)?berikut|\s+[^:]{0,200}?bahawa)?\s*:/iu',
        '/keputusan\s+(?:sebagaimana|seperti)\s+(?:yang\s+)?berikut\s*:/iu',
        '/fatwanya\s+(?:adalah\s+)?(?:sebagaimana|seperti)\s+(?:yang\s+)?berikut\s*:/iu',
        '/membuat[^:.]{0,150}?fatwa\s+(?:yang\s+)?berikut\s*:/iu',
        '/berpandangan\s+(?:sebagaimana|seperti)\s+(?:yang\s+)?berikut\s*[:;]/iu',
        '/(?:adalah|telah)\s+diputuskan\s+bahawa/iu',
        '/memutuskan\s+bahawa/iu',
        // Gazettes: "JADUAL" (or "JADUAL/SCHEDULE"), then the fatwa's title in capitals.
        '/\bJADUAL(?:\s*\/\s*SCHEDULE)?\b\.?\s*\n?(?:[ \t]*(?:[^a-z\n]{8,})?\n){0,6}/u',
        // Selangor: "... dalam Jadual sebagai fatwa dan disiarkan ... Enakmen itu." then the title.
        '/dalam\s+Jadual(?:\s+sebagai\s+fatwa[^.]*)?\.\s*(?:[ \t]*(?:[^a-z\n]{8,})?\n){0,6}/u',
        // Perlis: "Bil. 6/71 TAHUN 2024", then the title in capitals.
        '/(?i:Bil\.?\s*\d+\s*\/\s*\d+\s+Tahun\s+\d{4})\s*\n(?:[ \t]*(?:[^a-z\n]{8,})?\n){0,6}/u',
        // Perlis meeting notes: a "_____" or "-----" rule, the title in capitals, then the decision.
        '/\n[ \t]*[_\-]{10,}[ \t]*\n(?:[ \t]*(?:[^a-z\n]{4,})?\n){0,8}/u',
        // Kedah irsyad with no wording marker: everything after the heading and title.
        '/IRSYAD\s+AL-(?:HUKMI|FATWA)\s*\n(?:[ \t]*(?:[^a-z\n]{8,})?\n){0,6}/u',
    ];

    /** Where the decision ends: signatures, closing formulas, attendance lists. */
    private const END_MARKER = '/\n[ \t]*(?:Bertarikh\b|Dated\b|Diperbuat\b|Dibuat\b|-?\s*Tamat\s*-?|KEHADIRAN\b|Persetujuan oleh|Disediakan oleh|Sekian\b|\(?\s*MLFNS|TARIKH KEPUTUSAN|Dengan Titah Perintah|Fail Fatwa|S\.?\s?S\.?\s+DATU)|والله\s*اعلم|Wallahu?\s*a.?lam/iu';

    public static function extract(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = "\n".str_replace("\r", '', $text);

        foreach (self::START_MARKERS as $i => $marker) {
            if (! preg_match($marker, $text, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            // A "KEPUTUSAN" heading at the very top is the document's title
            // ("KEPUTUSAN MESYUARAT JAWATANKUASA FATWA..."), not the decision.
            if ($i === 0 && $m[0][1] < 100) {
                continue;
            }

            $passage = substr($text, $m[0][1] + strlen($m[0][0]));

            if (preg_match(self::END_MARKER, $passage, $end, PREG_OFFSET_CAPTURE)) {
                $passage = substr($passage, 0, $end[0][1]);
            }

            $passage = self::tidy($passage);

            if (mb_strlen($passage) >= self::MIN_CHARS) {
                return self::shorten($passage);
            }
        }

        return null;
    }

    /** Collapse the PDF layout spacing, keep paragraph and list breaks. */
    private static function tidy(string $passage): string
    {
        // Page furniture from gazettes: page numbers ("2", "1/2", "10k"), repeated headers ("2 P.U. (B) 318").
        $passage = preg_replace('/^[ \t]*[^\w\n(]{0,3}[ \t]*(?:\d+(?:\s*\/\s*\d+)?k?|(?:\d+\s+)?P\.\s?U\.\s*\([AB]\)\s*\d+|WARTA KERAJAAN[^\n]*)[ \t]*$/mu', '', $passage);
        $passage = preg_replace(["/[ \t]+/u", "/ *\n */u", "/\n{3,}/u"], [' ', "\n", "\n\n"], $passage);

        // Lines wrapped mid-sentence: join them, unless the next line starts a list item.
        $passage = preg_replace('/(?<![.:;])\n(?!\n|\s*(?:\d+\.|[a-z]\)|\([a-z0-9]+\)|[ivx]+\.|[-•]))/u', ' ', $passage);

        return trim($passage, " \n\t:");
    }

    /** Cut long decisions at a paragraph or sentence end. */
    private static function shorten(string $passage): string
    {
        if (mb_strlen($passage) <= self::MAX_CHARS) {
            return $passage;
        }

        $cut = mb_substr($passage, 0, self::MAX_CHARS);
        $at = max(mb_strrpos($cut, "\n") ?: 0, mb_strrpos($cut, '. ') ?: 0);

        return rtrim($at > self::MAX_CHARS / 2 ? mb_substr($cut, 0, $at + 1) : $cut).' …';
    }
}
