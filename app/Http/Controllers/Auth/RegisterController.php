<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/** Public sign-up. New accounts are ordinary users (never admins). */
class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique' => __('E-mel ini sudah didaftarkan.'),
        ]);

        $user = User::create($data + ['locale' => app()->getLocale()]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.edit')->with('success', __('Selamat datang, :name! Akaun anda telah dicipta.', ['name' => $user->name]));
    }
}
