<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Branch */
class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'opening_hours' => $this->opening_hours,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'updated_by' => $this->updated_by,
        ];
    }
}
