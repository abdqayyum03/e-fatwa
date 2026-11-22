<?php

namespace App\Http\Controllers;

use App\Services\FatwaAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

/** "Tanya AI" page: question in, cited summary of matching fatwas out. */
class AskController extends Controller
{
    public function index()
    {
        return view('ask.index', ['enabled' => FatwaAssistant::isConfigured()]);
    }

    public function store(Request $request, FatwaAssistant $assistant)
    {
        abort_unless(FatwaAssistant::isConfigured(), 503, __('Pembantu AI belum dikonfigurasi.'));

        $data = $request->validate(['question' => 'required|string|min:5|max:500']);

        try {
            $result = $assistant->ask($data['question']);
        } catch (\Throwable $e) {
            report($e);
            $message = __('Pembantu AI tidak dapat dihubungi buat masa ini. Sila cuba sebentar lagi.');

            return $request->wantsJson()
                ? response()->json(['message' => $message], 502)
                : back()->withInput()->with('error', $message);
        }

        $result['html'] = (string) $this->render($result['answer'], $result['sources']);

        if ($request->wantsJson()) {
            // Ready-made card, so the page and the JS show answers the same way.
            return response()->json($result + ['card' => view('ask._answer', ['result' => $result])->render()]);
        }

        return view('ask.index', [
            'enabled' => true,
            'question' => $data['question'],
            'result' => $result,
        ]);
    }

    /**
     * Plain-text answer -> safe HTML: escape everything, keep paragraphs and "-" bullets,
     * and turn [n] citations into links to the cited fatwa.
     */
    private function render(string $answer, array $sources): HtmlString
    {
        $byNumber = collect($sources)->keyBy('n');
        $html = '';
        $inList = false;

        foreach (preg_split('/\n/u', trim($answer)) as $line) {
            $line = trim($line);
            $isBullet = (bool) preg_match('/^[-•*]\s+/u', $line);
            $text = e(preg_replace('/^[-•*]\s+/u', '', $line));

            $text = preg_replace_callback('/\[(\d+)\]/', function ($m) use ($byNumber) {
                $source = $byNumber->get((int) $m[1]);

                return $source
                    ? sprintf('<a href="%s" class="cite" title="%s">[%d]</a>', e($source['url']), e($source['title']), $source['n'])
                    : ''; // drop citations to sources that don't exist
            }, $text);

            if ($isBullet) {
                $html .= ($inList ? '' : '<ul>').'<li>'.$text.'</li>';
                $inList = true;

                continue;
            }

            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }

            if ($line !== '') {
                $html .= '<p>'.$text.'</p>';
            }
        }

        return new HtmlString($html.($inList ? '</ul>' : ''));
    }
}
