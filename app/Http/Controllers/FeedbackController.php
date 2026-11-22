<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        // Public feedback form (submitted feedback is never shown publicly).
        return view('feedbacks.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'content' => 'required|string|max:2000',
        ]);

        Feedback::create($request->only(['name', 'email', 'phone_number', 'content']));

        return view('feedbacks.thankyou');
    }
}
