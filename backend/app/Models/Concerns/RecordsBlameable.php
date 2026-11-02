<?php

namespace App\Models\Concerns;

trait RecordsBlameable
{
    public static function bootRecordsBlameable(): void
    {
        static::creating(function ($model): void {
            $name = static::resolveBlameableName();
            if (! $model->created_by) {
                $model->created_by = $name;
            }
            if (! $model->updated_by) {
                $model->updated_by = $name;
            }
        });

        static::updating(function ($model): void {
            $model->updated_by = static::resolveBlameableName();
        });
    }

    protected static function resolveBlameableName(): string
    {
        $user = auth()->user();

        return $user?->name ?? 'System';
    }
}
