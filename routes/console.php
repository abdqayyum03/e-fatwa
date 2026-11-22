<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fatwa:token {email : Email of an admin user}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user || ! $user->is_admin) {
        $this->error("No admin user found with email {$email}.");

        return 1;
    }

    $token = $user->createToken('scraper')->plainTextToken;

    $this->info('API token created. Put it in scraper/.env as API_TOKEN (it is shown only once):');
    $this->line($token);
})->purpose('Create an API token for the fatwa scraper');

/*
| Scheduled jobs. They run only when Laravel's scheduler is running:
|   php artisan schedule:work        (keep a terminal open), or a cron/Task
|   Scheduler entry calling "php artisan schedule:run" every minute.
*/

// Pick up new fatwas from the mufti websites late on Sunday...
Schedule::command('fatwa:scrape --no-ansi')->weeklyOn(0, '23:00')->withoutOverlapping();

// ...and email subscribers about them on Monday morning (Malaysian time).
Schedule::command('fatwa:send-alerts')->weeklyOn(1, '08:00');
