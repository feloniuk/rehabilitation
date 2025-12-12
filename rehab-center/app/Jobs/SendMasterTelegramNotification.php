<?php

namespace App\Jobs;

use App\Services\MasterNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMasterTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointmentId;

    public function __construct($appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    public function handle(MasterNotificationService $masterNotificationService)
    {
        $appointment = Appointment::findOrFail($this->appointmentId);
        $masterNotificationService->processMasterNotificationQueue((object)[
            'payload' => json_encode([
                'job' => 'send_master_telegram_notification',
                'data' => [
                    'appointment_id' => $this->appointmentId,
                    'master_id' => $appointment->master_id
                ]
            ]),
            'delete' => function() {} // Заглушка для метода
        ]);
    }
}