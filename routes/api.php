<?php

use App\Http\Controllers\Api\FatwaApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| All API routes need a Sanctum token belonging to an admin.
| Create one with:  php artisan fatwa:token admin@example.com
| and send it as:   Authorization: Bearer <token>
*/

Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/fatwas/store', [FatwaApiController::class, 'store']);
});
