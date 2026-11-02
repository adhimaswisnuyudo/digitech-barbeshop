<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Booking */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'payment_method' => $this->payment_method,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'notes' => $this->notes,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'barber' => $this->whenLoaded('barber', fn () => [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'whatsapp_number' => $this->customer->whatsapp_number,
            ]),
            'service' => $this->whenLoaded('bookingServices', function () {
                $line = $this->bookingServices->first();

                return $line ? new ServiceResource($line->service) : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'updated_by' => $this->updated_by,
        ];
    }
}
