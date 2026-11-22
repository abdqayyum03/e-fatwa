<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Fatwa;
use App\Services\CategoryRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** /admin/kategori: rename, merge and delete categories; keyword rules for uncategorised fatwas. */
class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories', [
            'categories' => Category::withCount('fatwas')->with('rules')->orderBy('category_name')->get(),
            'rules' => CategoryRule::with('category')->orderBy('priority')->get(),
            'uncategorised' => Fatwa::whereNull('category_id')->count(),
            'preview' => session('preview'),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'category_name' => ['required', 'string', 'max:100', Rule::unique('categories', 'category_name')->ignore($category->id)],
        ]);

        $category->update($data);

        return back()->with('success', __('Kategori dinamakan semula.'));
    }

    /** Move every fatwa (and rule) of this category into another, then delete it. */
    public function merge(Request $request, Category $category)
    {
        $data = $request->validate([
            'target_id' => ['required', Rule::exists('categories', 'id'), Rule::notIn([$category->id])],
        ]);

        $target = Category::findOrFail($data['target_id']);

        DB::transaction(function () use ($category, $target) {
            Fatwa::where('category_id', $category->id)->update(['category_id' => $target->id]);
            CategoryRule::where('category_id', $category->id)->update(['category_id' => $target->id]);
            $category->delete();
        });

        return back()->with('success', __('":from" digabungkan ke dalam ":to".', ['from' => $category->category_name, 'to' => $target->category_name]));
    }

    public function destroy(Category $category)
    {
        if ($category->fatwas()->exists()) {
            return back()->with('error', __('Kategori yang masih mempunyai fatwa tidak boleh dipadam. Gabungkan ke kategori lain dahulu.'));
        }

        $category->delete();

        return back()->with('success', __('Kategori dipadam.'));
    }

    public function storeRule(Request $request)
    {
        CategoryRule::create($this->validateRule($request));

        return back()->with('success', __('Peraturan ditambah.'));
    }

    public function updateRule(Request $request, CategoryRule $rule)
    {
        $rule->update($this->validateRule($request));

        return back()->with('success', __('Peraturan dikemas kini.'));
    }

    public function destroyRule(CategoryRule $rule)
    {
        $rule->delete();

        return back()->with('success', __('Peraturan dipadam.'));
    }

    /** "Pratonton" shows what the rules would do; "Guna" saves it. */
    public function applyRules(Request $request, CategoryRules $rules)
    {
        $apply = $request->boolean('apply');
        $result = $rules->apply(dryRun: ! $apply);
        $total = collect($result['assigned'])->sum(fn ($list) => count($list));

        if ($apply) {
            return back()->with('success', __(':count fatwa telah diberi kategori.', ['count' => $total]));
        }

        return back()->with('preview', $result + ['total' => $total]);
    }

    private function validateRule(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'keywords' => 'required|string|max:2000',
            'priority' => 'required|integer|min:1|max:999',
        ]);
    }
}
