<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

/** /admin/maklum-balas: read, mark handled and delete feedback from the public form. */
class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $show = in_array($request->query('papar'), ['semua', 'selesai'], true) ? $request->query('papar') : 'baru';

        $feedback = Feedback::query()
            ->when($show === 'baru', fn ($q) => $q->whereNull('handled_at'))
            ->when($show === 'selesai', fn ($q) => $q->whereNotNull('handled_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.feedback', [
            'feedback' => $feedback,
            'show' => $show,
            'counts' => [
                'baru' => Feedback::whereNull('handled_at')->count(),
                'selesai' => Feedback::whereNotNull('handled_at')->count(),
                'semua' => Feedback::count(),
            ],
        ]);
    }

    public function update(Feedback $feedback)
    {
        $feedback->handled_at = $feedback->handled_at ? null : now();
        $feedback->save();

        return back()->with('success', $feedback->handled_at ? __('Ditanda sebagai selesai.') : __('Ditanda sebagai baru.'));
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return back()->with('success', __('Maklum balas dipadam.'));
    }
}
