<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\RecordsBlameable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'role',
    'name',
    'email',
    'password',
    'google_sub',
    'whatsapp_number',
    'branch_id',
    'created_by',
    'updated_by',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, RecordsBlameable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function barber(): HasOne
    {
        return $this->hasOne(Barber::class);
    }

    public function bookingsAsCustomer(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function hasWhatsApp(): bool
    {
        return filled($this->whatsapp_number);
    }
}
