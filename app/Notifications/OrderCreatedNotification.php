<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public string $forRole)
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
            'order_id' => $this->order->id,
            'order_date' => $this->order->orders_date,
            'patient_name' => $this->order->patient->name ?? 'Unknown Patient',
            'doctor_name' => $this->order->doctor->user->name ?? 'Unknown Doctor',
            'order_type' => $this->getOrderType(),
            'message' => $this->getCustomMessage($notifiable),
            'for_role' => $this->forRole,
            'action_url' => url('/orders/'.$this->order->id), // Placeholder URL
            'type' => 'order_created',
        ];
    }

    private function getOrderType(): string
    {
        if ($this->order->labs) {
            return 'Lab Order';
        }
        if ($this->order->radiology) {
            return 'Radiology Order';
        }
        if ($this->order->cp) {
            return 'Cardiopulmonary Order';
        }
        if ($this->order->referrals) {
            return 'Referral';
        }

        return 'Order';
    }

    private function getCustomMessage($notifiable): string
    {
        $type = $this->getOrderType();

        return "New {$type} received from Dr. {$this->order->doctor->user->name} for patient {$this->order->patient->name}.";
    }
}
