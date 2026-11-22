<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/** /admin/pengguna: list accounts, give or remove admin rights, disable accounts. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('cari'));

        $users = User::withCount('bookmarks')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderByDesc('is_admin')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users', ['users' => $users, 'search' => $search]);
    }

    /** action=admin toggles admin rights; action=disable toggles the account being disabled. */
    public function update(Request $request, User $user)
    {
        $action = $request->validate(['action' => 'required|in:admin,disable'])['action'];

        if ($user->is(auth()->user())) {
            return back()->with('error', __('Anda tidak boleh menukar akaun anda sendiri di sini.'));
        }

        // Never leave the site without an active admin.
        $removesAdmin = $user->is_admin && ($action === 'admin' || ! $user->isDisabled());
        if ($removesAdmin && User::where('is_admin', true)->whereNull('disabled_at')->count() <= 1) {
            return back()->with('error', __('Mesti ada sekurang-kurangnya seorang pentadbir aktif.'));
        }

        if ($action === 'admin') {
            $user->forceFill(['is_admin' => ! $user->is_admin])->save();
            $message = $user->is_admin ? __(':name kini pentadbir.', ['name' => $user->name]) : __(':name bukan lagi pentadbir.', ['name' => $user->name]);
        } else {
            $user->forceFill(['disabled_at' => $user->isDisabled() ? null : now()])->save();
            $message = $user->isDisabled() ? __('Akaun :name dinyahaktifkan.', ['name' => $user->name]) : __('Akaun :name diaktifkan semula.', ['name' => $user->name]);
        }

        return back()->with('success', $message);
    }
}
