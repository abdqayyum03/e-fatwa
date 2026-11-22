<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/** Weekly email listing fatwas added since the user's last alert. */
class NewFatwasDigest extends Mailable
{
    use Queueable;

    /** Fatwas listed in the email; the rest are linked to the website. */
    public const MAX_LISTED = 20;

    public function __construct(
        public User $user,
        public Collection $fatwas,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            // __() rather than trans_choice(): trans_choice falls back to English for Malay.
            subject: $this->fatwas->count() === 1
                ? __('1 fatwa baharu di e-Fatwa')
                : __(':count fatwa baharu di e-Fatwa', ['count' => $this->fatwas->count()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-fatwas',
            with: [
                'listed' => $this->fatwas->take(self::MAX_LISTED),
                'more' => max(0, $this->fatwas->count() - self::MAX_LISTED),
                // Sign only the path (checked with "signed:relative"), so the link keeps
                // working if the site is reached under a different host than APP_URL.
                'unsubscribeUrl' => url(URL::signedRoute('alerts.unsubscribe', ['user' => $this->user->id], absolute: false)),
            ],
        );
    }
}
