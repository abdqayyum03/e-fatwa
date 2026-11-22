<?php

namespace App\Console\Commands;

use App\Mail\NewFatwasDigest;
use App\Models\Fatwa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Email each subscriber the fatwas added since their last alert, filtered by
 * the categories and states they follow. Scheduled weekly in routes/console.php.
 */
class SendFatwaAlerts extends Command
{
    protected $signature = 'fatwa:send-alerts
                            {--dry-run : Show who would get what, without sending or updating anything}';

    protected $description = 'Send the weekly new-fatwa email to subscribed users';

    public function handle(): int
    {
        $users = User::where('alerts_enabled', true)->get();
        $sent = 0;

        foreach ($users as $user) {
            $since = $user->last_alert_sent_at ?? now()->subWeek();

            $fatwas = Fatwa::with('authority', 'category')
                ->where('created_at', '>', $since)
                ->when($user->alert_categories, fn ($q, $ids) => $q->whereIn('category_id', $ids))
                ->when($user->alert_states, fn ($q, $states) => $q->whereHas('authority', fn ($a) => $a->whereIn('state', $states)))
                ->latest()
                ->get();

            $this->line(sprintf('  %s: %d new fatwa(s)', $user->email, $fatwas->count()));

            if ($this->option('dry-run')) {
                continue;
            }

            if ($fatwas->isNotEmpty()) {
                Mail::to($user)->locale($user->locale ?: app()->getLocale())->send(new NewFatwasDigest($user, $fatwas));
                $sent++;
            }

            // Move the window forward even when nothing matched, so next week starts from now.
            $user->forceFill(['last_alert_sent_at' => now()])->save();
        }

        $this->info($this->option('dry-run')
            ? "Dry run: {$users->count()} subscriber(s) checked, nothing sent."
            : "Sent {$sent} email(s) to {$users->count()} subscriber(s).");

        return self::SUCCESS;
    }
}
