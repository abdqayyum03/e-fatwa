<?php

namespace App\Scrapers;

/**
 * Jabatan Mufti Negeri Kelantan - the department's own fatwa page links to an
 * official open-data set on data.gov.my (CC BY): fatwas issued 2015-2018, with
 * the full decision text. Columns:
 *   BIL | TAHUN | TAJUK KEPUTUSAN FATWA | TARIKH KEPUTUSAN | KEPUTUSAN / SIGHAH FATWA
 *   | KATEGORI | PEWARTAAN | TARIKH WARTA
 * (The department's website has no fatwa list of its own online.)
 */
class KelantanSource implements FatwaSource
{
    use Http;

    private const DATASET = 'https://archive.data.gov.my/data/dataset/senarai-fatwa-yang-dikeluarkan-oleh-jabatan-mufti-negeri-kelantan';

    private const CSV = 'https://archive.data.gov.my/data/dataset/3e788aff-0006-4d11-9409-20818c2e32e1/resource/833b1a3b-b1b3-4d40-8b07-8460709b3c03/download/senarai-fatwa-yang-dikeluarkan-oleh-jabatan-mufti-negeri-kelantan-tahun-2015-2018.csv';

    public function key(): string
    {
        return 'kelantan';
    }

    public function authority(): array
    {
        return ['authority_name' => 'Jabatan Mufti Negeri Kelantan', 'state' => 'Kelantan'];
    }

    public function fetch(): iterable
    {
        usleep($this->delayMs * 1000);
        $csv = $this->client()->get(self::CSV)->throw()->body();

        // The file is Windows-1252 (curly apostrophes in "Ulama'"), not UTF-8.
        if (! mb_check_encoding($csv, 'UTF-8')) {
            $csv = mb_convert_encoding($csv, 'UTF-8', 'Windows-1252');
        }
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csv);
        rewind($handle);

        while (($row = fgetcsv($handle, escape: '')) !== false) {
            // Data rows start with a running number in "BIL." and have a title.
            if (count($row) < 8 || ! ctype_digit(trim($row[0])) || trim($row[2]) === '') {
                continue;
            }

            [$no, $year, $title, $decidedOn, $decision, $category, $gazette, $gazettedOn] = array_map('trim', $row);
            $isGazetted = (bool) preg_match('/^warta/i', $gazette);

            yield [
                'title' => self::clean(rtrim($title, '. ')),
                'source_url' => self::DATASET.'#bil-'.$no,
                'pdf_url' => null,
                'full_text' => trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $decision))),
                'date_issued' => MalayDate::find($isGazetted ? $gazettedOn : $decidedOn)
                    ?? MalayDate::find($decidedOn) ?? ($year ?: null),
                'gazette_no' => null,
                'status' => $isGazetted ? 'Diwartakan' : 'Tidak Diwartakan',
                'category' => self::clean($category),
            ];
        }

        fclose($handle);
    }
}
