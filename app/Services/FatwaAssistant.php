<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\Fatwa;
use App\Support\CaBundle;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * "Tanya AI": answers a question using only fatwas found in our own database.
 *
 * 1. Full-text search finds the most relevant fatwas that have text.
 * 2. Their excerpts are numbered [1]..[n] and sent to Claude.
 * 3. Claude summarises what those fatwas say, citing [n], and never gives its
 *    own ruling. The caller turns [n] into links to the fatwa pages.
 */
class FatwaAssistant
{
    /** How many fatwas to give the model, and how much of each. */
    private const MAX_SOURCES = 6;

    private const EXCERPT_CHARS = 6000;

    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are the research assistant of e-Fatwa, a Malaysian website that collects fatwas published by the state mufti departments.

        Answer the user's question using ONLY the numbered fatwa excerpts provided in the user message. Each excerpt shows its state, year and title.

        Rules:
        - Summarise what the fatwas say. Do not issue your own ruling, add rulings from outside the excerpts, or present your own opinion as Islamic law.
        - Cite every statement with the excerpt number in square brackets, e.g. [1] or [2][3]. Only cite numbers that exist.
        - If states differ, say so and name each state.
        - If the excerpts do not answer the question, say clearly that the e-Fatwa database has no fatwa on it, and suggest contacting the relevant state mufti department. Do not guess.
        - For personal situations (family, money, worship), remind the user briefly to consult their state mufti department or a qualified scholar.
        - Excerpts may be cut off or contain text-extraction errors; do not invent missing parts.
        - Reply in the same language as the question (Malay or English). Use plain text with short paragraphs or simple "-" bullet points. No markdown headings, no tables.
        - Keep it under 250 words.
        PROMPT;

    public static function isConfigured(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /**
     * @return array{answer: string, sources: list<array{n:int, id:int, title:string, state:?string, year:?int, url:string}>, cached: bool}
     */
    public function ask(string $question): array
    {
        $question = trim(preg_replace('/\s+/u', ' ', $question));
        $cacheKey = 'ask:'.md5(mb_strtolower($question));

        if ($hit = Cache::get($cacheKey)) {
            return $hit + ['cached' => true];
        }

        $fatwas = $this->retrieve($question);

        if ($fatwas->isEmpty()) {
            return [
                'answer' => __('Tiada fatwa dalam pangkalan data e-Fatwa yang sepadan dengan soalan ini. Cuba gunakan kata kunci lain, atau hubungi jabatan mufti negeri anda.'),
                'sources' => [],
                'cached' => false,
            ];
        }

        $sources = $fatwas->values()->map(fn (Fatwa $f, int $i) => [
            'n' => $i + 1,
            'id' => $f->id,
            'title' => $f->title,
            'state' => $f->authority->state ?? null,
            'year' => $f->issued_year,
            'url' => route('fatwas.show', $f),
        ])->all();

        $answer = $this->callClaude($question, $fatwas);
        $result = ['answer' => $answer, 'sources' => $sources];

        // Same question again within a day costs nothing.
        Cache::put($cacheKey, $result, now()->addDay());

        return $result + ['cached' => false];
    }

    /** Most relevant fatwas that have text (MySQL natural-language FULLTEXT orders by relevance). */
    private function retrieve(string $question): Collection
    {
        return Fatwa::with('authority')
            ->whereNotNull('full_text')
            ->search($question)
            ->limit(self::MAX_SOURCES)
            ->get();
    }

    private function callClaude(string $question, Collection $fatwas): string
    {
        $excerpts = $fatwas->values()->map(function (Fatwa $f, int $i) {
            $text = Str::limit(preg_replace("/\n{3,}/u", "\n\n", (string) $f->full_text), self::EXCERPT_CHARS, ' [...]');

            return sprintf(
                "<fatwa number=\"%d\" state=\"%s\" year=\"%s\">\nTitle: %s\n\n%s\n</fatwa>",
                $i + 1,
                $f->authority->state ?? 'unknown',
                $f->issued_year ?? 'unknown',
                $f->title,
                $text,
            );
        })->implode("\n\n");

        $message = $this->client()->beta->messages->create(
            model: config('services.anthropic.model'),
            maxTokens: 4000,
            system: self::SYSTEM_PROMPT,
            // Q&A over a handful of excerpts: medium effort keeps answers careful without long waits.
            outputConfig: ['effort' => 'medium'],
            messages: [[
                'role' => 'user',
                'content' => "<fatwas>\n{$excerpts}\n</fatwas>\n\nQuestion: {$question}",
            ]],
            // If the model declines on a safety classifier, the API retries on a suitable
            // fallback model within the same call ("default" picks it by refusal category).
            fallbacks: 'default',
            betas: ['server-side-fallback-2026-07-01'],
        );

        if ($message->stopReason === 'refusal') {
            return __('Maaf, soalan ini tidak dapat dijawab oleh pembantu AI. Sila rujuk fatwa berkaitan secara terus atau hubungi jabatan mufti negeri anda.');
        }

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return trim($text) !== '' ? trim($text) : __('Pembantu AI tidak memberikan jawapan. Sila cuba lagi.');
    }

    private function client(): Client
    {
        return new Client(
            apiKey: config('services.anthropic.key'),
            requestOptions: [
                // Same CA bundle as the rest of the app, so HTTPS works whatever php.ini says.
                'transporter' => new GuzzleClient(['verify' => CaBundle::path()]),
                'timeout' => 90.0,
                'maxRetries' => 2,
            ],
        );
    }
}
