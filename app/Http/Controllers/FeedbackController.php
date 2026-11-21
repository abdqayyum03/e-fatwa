<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::latest()->paginate(10);
        return view('feedbacks.index', compact('feedbacks'));
    }

    public function create()
    {
        return view('feedbacks.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone_number' => 'required|string|max:20',
        'content' => 'required|string',
    ]);

    \App\Models\Feedback::create($request->all());

    return view('feedbacks.thankyou');
}
}
