<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Fatwa;

class FatwaApiController extends Controller
{
    public function store(Request $request)
    {
        // Manual validation using Validator to return JSON-friendly errors
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issuing_agency' => 'nullable|exists:authorities,id', // ✅ nullable
            'date_issued' => 'nullable|string',                    // ✅ only store year like "2024"
            'category_id' => 'nullable|exists:categories,id',
        ]);
        

        // If validation fails, return JSON error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the Fatwa
        $fatwa = Fatwa::create($validator->validated());

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Fatwa created successfully.',
            'data' => $fatwa,
        ], 201);
    }
}
