<?php

namespace App\Scrapers;

/** Every fatwa source the importer knows, keyed by its short name ("wp", "johor"...). */
class Sources
{
    /** @return array<string, FatwaSource> */
    public static function all(): array
    {
        $sources = [
            new WilayahPersekutuanSource, new PerakSource, new SelangorSource, new NegeriSembilanSource,
            new KedahSource, new PerlisSource, new PenangSource,
            new MelakaSource, new KelantanSource, new JohorSource,
            new SabahSource, new SarawakSource,
        ];

        return collect($sources)->keyBy(fn (FatwaSource $s) => $s->key())->all();
    }

    /** @return array<string, string> source key => state name, sorted by state */
    public static function states(): array
    {
        return collect(self::all())
            ->map(fn (FatwaSource $s) => $s->authority()['state'])
            ->sort()
            ->all();
    }
}
