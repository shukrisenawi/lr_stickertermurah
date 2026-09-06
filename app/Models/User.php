<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'no_tel', 'email', 'google_id', 'password', 'must_change_password', 'is_admin', 'avatar_path', 'discount_amount', 'discount_forever'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'must_change_password' => 'boolean',
            'is_admin' => 'boolean',
            'discount_amount' => 'decimal:2',
            'discount_forever' => 'boolean',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function markLoggedIn(): void
    {
        $now = now();

        $this->forceFill([
            'last_login_at' => $now,
            'last_seen_at' => $now,
        ])->saveQuietly();
    }

    public function markLastSeen(): void
    {
        $now = now();

        if ($this->last_seen_at?->greaterThan($now->copy()->subMinutes(5))) {
            return;
        }

        static::query()->whereKey($this->getKey())->update(['last_seen_at' => $now]);
        $this->setAttribute('last_seen_at', $now);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customerProjects(): HasMany
    {
        return $this->hasMany(CustomerProject::class)->latest();
    }

    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->latest('updated_at');
    }

    public function defaultCustomerAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->latestOfMany('updated_at');
    }

    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function googleContactConnection(): HasOne
    {
        return $this->hasOne(GoogleContactConnection::class);
    }
}
