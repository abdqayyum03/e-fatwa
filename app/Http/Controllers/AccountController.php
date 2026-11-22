<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/** Profile, alert preferences and password for the signed-in user. */
class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('account.edit', [
            'user' => $request->user(),
            'categories' => Category::orderBy('category_name')->get(),
            'states' => Authority::has('fatwas')->orderBy('state')->pluck('state')->unique()->values(),
            'bookmarkCount' => $request->user()->bookmarks()->count(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'alerts_enabled' => 'boolean',
            'alert_categories' => 'array',
            'alert_categories.*' => 'integer|exists:categories,id',
            'alert_states' => 'array',
            'alert_states.*' => 'string|exists:authorities,state',
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'alerts_enabled' => $request->boolean('alerts_enabled'),
            'alert_categories' => array_values(array_map('intval', $data['alert_categories'] ?? [])),
            'alert_states' => array_values($data['alert_states'] ?? []),
        ]);

        return back()->with('success', __('Tetapan disimpan.'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => __('Kata laluan semasa tidak betul.'),
        ]);

        $request->user()->update(['password' => $request->password]); // hashed by the model cast

        return back()->with('success', __('Kata laluan dikemas kini.'));
    }
}
