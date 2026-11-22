<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Authority;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** /admin/agensi: edit the religious authorities (name, logo, address, website). */
class AuthorityController extends Controller
{
    public function index()
    {
        return view('admin.authorities.index', [
            'authorities' => Authority::withCount('fatwas')->orderBy('state')->get(),
        ]);
    }

    public function edit(Authority $authority)
    {
        return view('admin.authorities.edit', ['authority' => $authority]);
    }

    public function update(Request $request, Authority $authority)
    {
        $data = $request->validate([
            'authority_name' => ['required', 'string', 'max:255', Rule::unique('authorities', 'authority_name')->ignore($authority->id)],
            'contact_info' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'poscode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'remove_logo' => 'sometimes|boolean',
        ]);

        // The state is the agency's key for the importers, so it isn't editable here.
        $authority->fill(collect($data)->except(['logo', 'remove_logo'])->all());

        if ($request->boolean('remove_logo')) {
            $authority->logo = null;
        }

        if ($file = $request->file('logo')) {
            $name = Str::slug($authority->state).'-'.now()->format('YmdHis').'.'.$file->extension();
            $file->move(public_path('images/authorities'), $name);
            $authority->logo = $name;
        }

        $authority->save();

        return redirect()->route('admin.authorities.index')->with('success', __('Agensi ":name" dikemas kini.', ['name' => $authority->authority_name]));
    }
}
