<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\UserDevice;
use App\Services\FcmService;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Kirim push reminder booking sesuai config booking.reminder_hours';

    public function handle(FcmService $fcm): int
    {
        foreach (config('booking.reminder_hours') as $hours) {
            $this->sendForOffset($fcm, (int) $hours);
        }

        return self::SUCCESS;
    }

    private function sendForOffset(FcmService $fcm, int $hours): void
    {
        $target = now()->addHours($hours);
        $windowStart = $target->copy()->subMinutes(15);
        $windowEnd = $target->copy()->addMinutes(15);

        $type = "booking_reminder_{$hours}h";

        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereBetween('start_at', [$windowStart, $windowEnd])
            ->with('customer')
            ->get();

        foreach ($bookings as $booking) {
            $already = Notification::query()
                ->where('user_id', $booking->customer_id)
                ->where('type', $type)
                ->whereJsonContains('payload', ['booking_id' => $booking->id])
                ->exists();

            if ($already) {
                continue;
            }

            $tokens = UserDevice::query()
                ->where('user_id', $booking->customer_id)
                ->pluck('fcm_token');

            foreach ($tokens as $token) {
                $fcm->sendToToken(
                    $token,
                    'Reminder booking',
                    'Janji temu '.$hours.' jam lagi: '.$booking->start_at->timezone(config('app.timezone'))->format('d M Y H:i'),
                    ['booking_id' => $booking->id, 'type' => $type],
                );
            }

            Notification::create([
                'user_id' => $booking->customer_id,
                'type' => $type,
                'payload' => ['booking_id' => $booking->id],
                'created_by' => 'System',
                'updated_by' => 'System',
            ]);
        }
    }
}
