<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\UserDevice;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendBookingConfirmedPush implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $bookingId,
    ) {}

    public function handle(FcmService $fcm): void
    {
        $booking = Booking::query()->with('customer')->find($this->bookingId);
        if (! $booking || ! $booking->customer) {
            return;
        }

        $tokens = UserDevice::query()
            ->where('user_id', $booking->customer_id)
            ->pluck('fcm_token');

        foreach ($tokens as $token) {
            $fcm->sendToToken(
                $token,
                'Booking dikonfirmasi',
                'Janji temu Anda '.$booking->start_at->timezone(config('app.timezone'))->format('d M Y H:i'),
                ['booking_id' => $booking->id, 'type' => 'booking_confirmed'],
            );
        }
    }
}
