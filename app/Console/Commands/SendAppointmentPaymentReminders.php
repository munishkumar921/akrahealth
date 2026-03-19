<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentPaymentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders to patients for upcoming appointments that are not yet paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $reminderSent = 0;
        $reminderSkipped = 0;

        // Get appointments that need payment reminders:
        // 1. Upcoming appointments (within next 7 days)
        // 2. Payment status is pending
        // 3. Status is confirmed or pending
        // 4. Either no reminder sent yet OR last reminder was more than 6 hours ago
        $appointments = Appointment::where('appointment_date', '>=', $now->toDateString())
            ->where('appointment_date', '<=', $now->copy()->addDays(7)->toDateString())
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('payment_status', 'pending')
            ->where(function ($query) use ($now) {
                $query->whereNull('last_payment_reminder_at')
                    ->orWhere('last_payment_reminder_at', '<=', $now->copy()->subHours(6));
            })
            ->where(function ($query) {
                // Only send if fee_amount > 0
                $query->where('fee_amount', '>', 0)
                    ->orWhere('total_amount', '>', 0);
            })
            ->with(['patient.user', 'doctor.user'])
            ->get();

        $this->info('Found '.$appointments->count().' appointments that may need payment reminders.');

        foreach ($appointments as $appointment) {
            // Skip if no patient or user
            if (! $appointment->patient || ! $appointment->patient->user) {
                $reminderSkipped++;

                continue;
            }

            // Calculate hours until appointment
            $appointmentDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d').' '.$appointment->appointment_time);
            $hoursUntilAppointment = $now->diffInHours($appointmentDateTime, false);

            // Send reminders at specific intervals:
            // - 24 hours before
            // - 12 hours before
            // - 6 hours before
            // - 1 hour before
            $shouldSend = false;
            $reminderReason = '';

            if ($hoursUntilAppointment <= 24 && $hoursUntilAppointment > 22) {
                $shouldSend = true;
                $reminderReason = '24 hours before appointment';
            } elseif ($hoursUntilAppointment <= 12 && $hoursUntilAppointment > 10) {
                $shouldSend = true;
                $reminderReason = '12 hours before appointment';
            } elseif ($hoursUntilAppointment <= 6 && $hoursUntilAppointment > 4) {
                $shouldSend = true;
                $reminderReason = '6 hours before appointment';
            } elseif ($hoursUntilAppointment <= 1 && $hoursUntilAppointment > 0) {
                $shouldSend = true;
                $reminderReason = '1 hour before appointment';
            } elseif ($hoursUntilAppointment < 0) {
                // Appointment is in the past or happening now - send urgent reminder
                $shouldSend = true;
                $reminderReason = 'urgent - appointment time approaching';
            }

            if ($shouldSend) {
                try {
                    // Send notification (both email and database)
                    $appointment->patient->user->notify(new AppointmentPaymentReminder($appointment));

                    // Update appointment tracking
                    $appointment->update([
                        'last_payment_reminder_at' => $now,
                        'payment_reminder_count' => ($appointment->payment_reminder_count ?? 0) + 1,
                    ]);

                    $reminderSent++;
                    $this->line("✓ Payment reminder sent to {$appointment->patient->user->email} for appointment on {$appointment->appointment_date->format('Y-m-d')} at {$appointment->appointment_time} ({$reminderReason})");

                    Log::info('Payment reminder sent', [
                        'appointment_id' => $appointment->id,
                        'patient_email' => $appointment->patient->user->email,
                        'reminder_count' => $appointment->payment_reminder_count,
                        'hours_until' => $hoursUntilAppointment,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send payment reminder', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("✗ Failed to send reminder to {$appointment->patient->user->email}: {$e->getMessage()}");
                }
            } else {
                $reminderSkipped++;
            }
        }

        $this->info("Payment reminders sent: {$reminderSent}, Skipped: {$reminderSkipped}");

        if ($reminderSent > 0) {
            $this->info('Appointment payment reminders processed successfully.');
        }
    }
}
