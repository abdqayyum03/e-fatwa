<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AlertUnsubscribeController;
use App\Http\Controllers\AskController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FatwaController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LocaleController;
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

Route::get('/tanya', [AskController::class, 'index'])->name('ask.index');
Route::get('/bahasa/{locale}', LocaleController::class)->name('locale');

// Signed link from the alert email: works without logging in, only for that user.
Route::get('/makluman/berhenti/{user}', AlertUnsubscribeController::class)
    ->middleware('signed:relative')->name('alerts.unsubscribe');

/*
|--------------------------------------------------------------------------
| Accounts
|--------------------------------------------------------------------------
| Anyone can register an ordinary account (bookmarks, alerts, Tanya AI).
| Admin accounts come only from the AdminUserSeeder (ADMIN_* in .env).
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/akaun', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/akaun', [AccountController::class, 'update'])->name('account.update');
    Route::put('/akaun/kata-laluan', [AccountController::class, 'updatePassword'])->middleware('throttle:5,1')->name('account.password');

    Route::get('/akaun/simpanan', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/fatwas/{fatwa}/simpan', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    Route::post('/tanya', [AskController::class, 'store'])->middleware('throttle:ask')->name('ask.store');
});

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
