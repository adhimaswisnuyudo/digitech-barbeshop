<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends BaseModel
{
    protected $fillable = [
        'user_id',
        'fcm_token',
        'platform',
        'created_by',
        'updated_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
