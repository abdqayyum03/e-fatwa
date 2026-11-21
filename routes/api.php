<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FatwaApiController;

Route::post('/user', function (Request $request) {
    return $request->user();
});

Route::post('/fatwas/store', [FatwaApiController::class, 'store']);

