<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * One-click unsubscribe from the link in the alert email. The URL is signed,
 * so it only works for the user it was generated for (no login needed).
 */
class AlertUnsubscribeController extends Controller
{
    public function __invoke(User $user)
    {
        $user->update(['alerts_enabled' => false]);

        return view('account.unsubscribed', ['user' => $user]);
    }
}
