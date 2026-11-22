<?php

namespace App\Http\Controllers;

use App\Models\Fatwa;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /** "Simpanan saya": the user's saved fatwas, newest first. */
    public function index(Request $request)
    {
        $fatwas = $request->user()->bookmarks()
            ->with('authority', 'category')
            ->orderByPivot('created_at', 'desc')
            ->paginate(15);

        return view('account.bookmarks', compact('fatwas'));
    }

    /** Save or un-save a fatwa. Returns JSON for the button, or redirects back. */
    public function toggle(Request $request, Fatwa $fatwa)
    {
        $result = $request->user()->bookmarks()->toggle($fatwa->id);
        $saved = ! empty($result['attached']);

        if ($request->wantsJson()) {
            return response()->json(['saved' => $saved]);
        }

        return back()->with('success', $saved ? __('Fatwa disimpan.') : __('Fatwa dibuang daripada simpanan.'));
    }
}
