<?php

namespace App\Models;

use App\Models\Concerns\RecordsBlameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasUuids, RecordsBlameable;

    public $incrementing = false;

    protected $keyType = 'string';
}
