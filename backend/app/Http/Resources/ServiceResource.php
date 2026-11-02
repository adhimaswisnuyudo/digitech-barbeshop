<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'updated_by' => $this->updated_by,
        ];
    }
}
