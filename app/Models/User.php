<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'alerts_enabled',
        'alert_categories',
        'alert_states',
        'locale',
        // is_admin is deliberately NOT here: no form can make a user an admin.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'alerts_enabled' => 'boolean',
            'alert_categories' => 'array',
            'alert_states' => 'array',
            'last_alert_sent_at' => 'datetime',
        ];
    }

    /** Fatwas this user has saved. */
    public function bookmarks()
    {
        return $this->belongsToMany(Fatwa::class, 'bookmarks')->withTimestamps();
    }

    public function hasBookmarked(Fatwa $fatwa): bool
    {
        return $this->bookmarks()->whereKey($fatwa->getKey())->exists();
    }
}
