<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Authority;
use App\Models\Category;
use App\Models\Fatwa;
use App\Services\FatwaQuality;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** /admin/kualiti: fatwas that need a human look, with quick fixes. */
class QualityController extends Controller
{
    public function index(Request $request, FatwaQuality $quality)
    {
        $issue = array_key_exists($request->query('isu'), FatwaQuality::ISSUES) ? $request->query('isu') : 'no_category';
        $state = $request->query('negeri') ?: null;

        $fatwas = $quality->query($issue, $state)
            ->with(['authority', 'category'])
            // Duplicates read best side by side: same agency, same title together.
            ->when($issue === 'duplicates', fn ($q) => $q->orderBy('issuing_agency')->orderBy('title'), fn ($q) => $q->latest('id'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.quality', [
            'issue' => $issue,
            'state' => $state,
            'counts' => $quality->counts(),
            'fatwas' => $fatwas,
            'states' => Authority::whereHas('fatwas')->orderBy('state')->pluck('state'),
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    /** Quick fix from a queue row: set title / category / date, and/or mark as checked. */
    public function update(Request $request, Fatwa $fatwa)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:500',
            'category_id' => ['sometimes', 'nullable', Rule::exists('categories', 'id')],
            'date_issued' => 'sometimes|nullable|string|max:100',
            'checked' => 'sometimes|boolean',
        ]);

        $fatwa->fill(collect($data)->except('checked')->all());

        if (array_key_exists('checked', $data)) {
            $fatwa->quality_checked_at = $data['checked'] ? now() : null;
        }

        $fatwa->save();

        return back()->with('success', __('Fatwa ":title" dikemas kini.', ['title' => \Illuminate\Support\Str::limit($fatwa->title, 60)]));
    }
}
