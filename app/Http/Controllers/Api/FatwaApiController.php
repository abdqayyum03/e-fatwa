<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fatwa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Import endpoint for external tools. The built-in importer is
 * `php artisan fatwa:scrape`; this API is for anything else.
 * Sending a fatwa with a source_url that already exists updates it.
 */
class FatwaApiController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'nullable|string',
            'source_url' => 'nullable|url|max:500',
            'pdf_url' => 'nullable|url|max:500',
            'full_text' => 'nullable|string',
            'issuing_agency' => 'nullable|exists:authorities,id',
            'category_id' => 'nullable|exists:categories,id',
            'date_issued' => 'nullable|string|max:50',
            'gazette_no' => 'nullable|string|max:100',
            'status' => 'nullable|in:Diwartakan,Tidak Diwartakan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $fatwa = ! empty($data['source_url'])
            ? Fatwa::updateOrCreate(['source_url' => $data['source_url']], $data)
            : Fatwa::create($data);

        return response()->json([
            'success' => true,
            'message' => $fatwa->wasRecentlyCreated ? 'Fatwa created successfully.' : 'Fatwa updated successfully.',
            'data' => $fatwa,
        ], $fatwa->wasRecentlyCreated ? 201 : 200);
    }
}
