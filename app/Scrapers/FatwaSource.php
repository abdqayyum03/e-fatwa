<?php

namespace App\Scrapers;

/**
 * One adapter per fatwa website. Each adapter only knows how to read its own
 * site; saving, de-duplicating and PDF text extraction live in FatwaImporter.
 */
interface FatwaSource
{
    /** Short key used on the command line, e.g. "selangor". */
    public function key(): string;

    /** Authority details; matched to the authorities table by name. */
    public function authority(): array;

    /**
     * Yield one array per fatwa with these keys (only title and source_url required):
     *   title, source_url, pdf_url, date_issued, gazette_no, status, category,
     *   full_text (only for sites that publish the fatwa as web text, not PDF)
     *
     * @return iterable<array<string, string|null>>
     */
    public function fetch(): iterable;
}
