<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients for upcoming appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $end = $now->copy()->addHour();

        // Get appointments within the next hour
        $appointments = Appointment::where(function ($query) use ($now, $end) {
            if ($now->toDateString() === $end->toDateString()) {
                $query->where('appointment_date', $now->toDateString())
                    ->where('appointment_time', '>=', $now->format('H:i:s'))
                    ->where('appointment_time', '<=', $end->format('H:i:s'));
            } else {
                $query->where(function ($q) use ($now) {
                    $q->where('appointment_date', $now->toDateString())
                        ->where('appointment_time', '>=', $now->format('H:i:s'));
                })->orWhere(function ($q) use ($end) {
                    $q->where('appointment_date', $end->toDateString())
                        ->where('appointment_time', '<=', $end->format('H:i:s'));
                });
            }
        })
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->with(['patient.user', 'doctor.user'])
            ->get();

        $this->info('Sending reminders for '.$appointments->count().' appointments within the next hour.');

        foreach ($appointments as $appointment) {
            // Notify patient
            if ($appointment->patient && $appointment->patient->user) {
                $appointment->patient->user->notify(new AppointmentReminder($appointment, 'patient'));
                $this->line('Reminder sent to patient '.$appointment->patient->user->email.' for appointment on '.Carbon::parse($appointment->appointment_date)->format('Y-m-d').' at '.$appointment->appointment_time);
            }

            // Notify doctor
            if ($appointment->doctor && $appointment->doctor->user) {
                $appointment->doctor->user->notify(new AppointmentReminder($appointment, 'doctor'));
                $this->line('Reminder sent to doctor '.$appointment->doctor->user->email.' for appointment on '.Carbon::parse($appointment->appointment_date)->format('Y-m-d').' at '.$appointment->appointment_time);
            }
        }

        $this->info('Appointment reminders sent successfully.');
    }
}
