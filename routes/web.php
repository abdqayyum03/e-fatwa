<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FatwaController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\MosqueController;
use App\Http\Controllers\PrayerTimeController;
use App\Http\Controllers\PreacherController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/dashboard', fn () => redirect()->route('dashboard'));

Route::get('/search-fatwa', [FatwaController::class, 'search'])->name('fatwas.search');
Route::get('/bandingkan', [FatwaController::class, 'compare'])->name('fatwas.compare');
Route::get('/statistik', StatisticsController::class)->name('statistics');
Route::get('/waktu-solat', PrayerTimeController::class)->middleware('throttle:60,1')->name('prayer-times');
Route::get('/fatwa-suggest', [FatwaController::class, 'suggest'])->middleware('throttle:60,1')->name('fatwas.suggest');
Route::get('/search-preacher', [PreacherController::class, 'search'])->name('preachers.search');
Route::get('/mosques/map', [MosqueController::class, 'map'])->name('mosques.map');

Route::get('/feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
Route::post('/feedbacks', [FeedbackController::class, 'store'])->name('feedbacks.store');

/*
|--------------------------------------------------------------------------
| Admin login
|--------------------------------------------------------------------------
| There is no public registration. Admin accounts are created by the
| AdminUserSeeder from the ADMIN_* values in .env.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin-only: create, edit and delete
|--------------------------------------------------------------------------
| Registered before the public "show" routes so that /fatwas/create is not
| captured by /fatwas/{fatwa}.
*/

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::resource('fatwas', FatwaController::class)->except(['index', 'show']);
    Route::resource('preachers', PreacherController::class)->except(['index', 'show']);
    Route::resource('mosques', MosqueController::class)->except(['index', 'show']);
    Route::resource('kuliah', LectureController::class)->except(['index', 'show'])
        ->names('lectures')->parameters(['kuliah' => 'lecture']);
});

/*
|--------------------------------------------------------------------------
| Public browsing
|--------------------------------------------------------------------------
*/

Route::resource('fatwas', FatwaController::class)->only(['index', 'show']);
Route::resource('authorities', AuthorityController::class)->only(['index', 'show']);
Route::resource('preachers', PreacherController::class)->only(['index', 'show']);
Route::resource('mosques', MosqueController::class)->only(['index']);
Route::resource('kuliah', LectureController::class)->only(['index', 'show'])
    ->names('lectures')->parameters(['kuliah' => 'lecture']);
Route::get('/kuliah/{lecture}/kalendar.ics', [LectureController::class, 'ics'])->name('lectures.ics');
