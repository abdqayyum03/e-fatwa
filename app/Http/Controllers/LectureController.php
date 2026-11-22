<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Mosque;
use App\Models\Preacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LectureController extends Controller
{
    /** Upcoming (or past) kuliah, filterable by mosque, preacher and type. */
    public function index(Request $request)
    {
        $filters = $request->only(['mosque', 'preacher', 'type', 'past']);
        $past = ! empty($filters['past']);

        $query = Lecture::with('mosque', 'preacher')
            ->when($filters['mosque'] ?? null, fn ($q, $id) => $q->where('mosque_id', $id))
            ->when($filters['preacher'] ?? null, fn ($q, $id) => $q->where('preacher_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type));

        $lectures = ($past
            ? $query->where('starts_at', '<', now()->startOfDay())->orderByDesc('starts_at')
            : $query->upcoming())
            ->paginate(20)
            ->withQueryString();

        // Group the current page by day for the agenda view.
        $days = $lectures->getCollection()->groupBy(fn (Lecture $l) => $l->starts_at->toDateString());

        return view('lectures.index', [
            'lectures' => $lectures,
            'days' => $days,
            'filters' => $filters,
            'past' => $past,
            'mosques' => Mosque::orderBy('name')->get(['id', 'name']),
            'preachers' => Preacher::orderBy('preacher_name')->get(['id', 'preacher_name']),
        ]);
    }

    public function show(Lecture $lecture)
    {
        $lecture->load('mosque', 'preacher');

        $more = Lecture::with('mosque')
            ->upcoming()
            ->where('id', '!=', $lecture->id)
            ->where(fn ($q) => $q
                ->when($lecture->mosque_id, fn ($q) => $q->orWhere('mosque_id', $lecture->mosque_id))
                ->when($lecture->preacher_id, fn ($q) => $q->orWhere('preacher_id', $lecture->preacher_id)))
            ->when(! $lecture->mosque_id && ! $lecture->preacher_id, fn ($q) => $q->whereRaw('1 = 0'))
            ->limit(5)
            ->get();

        return view('lectures.show', compact('lecture', 'more'));
    }

    /** "Add to calendar": a one-event .ics file any calendar app can import. */
    public function ics(Lecture $lecture)
    {
        $lecture->load('mosque', 'preacher');

        $escape = fn (?string $s) => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], (string) $s);
        $utc = fn ($dt) => $dt->copy()->utc()->format('Ymd\THis\Z');
        $end = $lecture->ends_at ?? $lecture->starts_at->copy()->addHour();

        $description = trim(collect([
            $lecture->preacher ? 'Penceramah: '.$lecture->preacher->preacher_name : null,
            $lecture->description,
            route('lectures.show', $lecture),
        ])->filter()->join("\n\n"));

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//e-Fatwa//Kuliah//MS',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:lecture-'.$lecture->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$utc(now()),
            'DTSTART:'.$utc($lecture->starts_at),
            'DTEND:'.$utc($end),
            'SUMMARY:'.$escape($lecture->type.': '.$lecture->title),
            'LOCATION:'.$escape(collect([$lecture->place(), $lecture->mosque?->location])->filter()->join(', ')),
            'DESCRIPTION:'.$escape($description),
            'END:VEVENT',
            'END:VCALENDAR',
        ])."\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kuliah-'.Str::slug($lecture->title).'.ics"',
        ]);
    }

    public function create(Request $request)
    {
        return view('lectures.create', $this->formOptions() + [
            'prefill' => $request->only(['mosque_id', 'preacher_id']),
        ]);
    }

    public function store(Request $request)
    {
        $lecture = Lecture::create($this->validated($request));

        return redirect()->route('lectures.show', $lecture)->with('success', 'Kuliah berjaya ditambah.');
    }

    public function edit(Lecture $lecture)
    {
        return view('lectures.edit', $this->formOptions() + compact('lecture'));
    }

    public function update(Request $request, Lecture $lecture)
    {
        $lecture->update($this->validated($request));

        return redirect()->route('lectures.show', $lecture)->with('success', 'Kuliah berjaya dikemas kini.');
    }

    public function destroy(Lecture $lecture)
    {
        $lecture->delete();

        return redirect()->route('lectures.index')->with('success', 'Kuliah berjaya dipadam.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', Lecture::TYPES),
            'description' => 'nullable|string|max:5000',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'mosque_id' => 'nullable|exists:mosques,id',
            'preacher_id' => 'nullable|exists:preachers,id',
            'venue' => 'nullable|required_without:mosque_id|string|max:255',
        ], [
            'venue.required_without' => 'Pilih masjid atau nyatakan tempat.',
            'ends_at.after' => 'Waktu tamat mesti selepas waktu mula.',
        ]);
    }

    private function formOptions(): array
    {
        return [
            'mosques' => Mosque::orderBy('name')->get(['id', 'name', 'location']),
            'preachers' => Preacher::orderBy('preacher_name')->get(['id', 'preacher_name']),
        ];
    }
}
