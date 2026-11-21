<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FatwaController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\PreacherController;
use App\Http\Controllers\MosqueController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return redirect('/');
})->name('dashboard');


Route::get('/search-fatwa', [FatwaController::class, 'search'])->name('fatwas.search');
Route::get('/mosques/map', [MosqueController::class, 'map'])->name('mosques.map');

Route::resource('authorities', AuthorityController::class)->only(['index', 'show']);
Route::resource('fatwas', FatwaController::class);
Route::get('/fatwas', [FatwaController::class, 'index'])->name('fatwas.index');
Route::resource('authorities', AuthorityController::class);
Route::resource('preachers', PreacherController::class);
Route::resource('mosques', MosqueController::class);
Route::resource('feedbacks', FeedbackController::class)->only(['create', 'store']);
Route::resource('categories', CategoryController::class);
Route::resource('feedbacks', FeedbackController::class)->only(['index', 'create', 'store']);

