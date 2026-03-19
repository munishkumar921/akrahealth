<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PrescriptionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Prescription $prescription)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'prescription_id' => $this->prescription->id,
            'medication' => $this->prescription->medication,
            'dosage' => $this->prescription->dosage.' '.$this->prescription->dosage_unit,
            'doctor_name' => $this->prescription->doctor->user->name ?? 'Unknown Doctor',
            'message' => $this->getCustomMessage($notifiable),
            'action_url' => route('prescription.show', $this->prescription->id),
            'type' => 'prescription_created',
        ];
    }

    private function getCustomMessage($notifiable): string
    {
        return "You have a new prescription for {$this->prescription->medication} prescribed by Dr. {$this->prescription->doctor->user->name}.";
    }
}
