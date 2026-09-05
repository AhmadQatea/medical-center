<?php

namespace App\Services;

use App\Actions\Booking\GenerateBookingConfirmationMessage;
use App\Actions\Booking\GeneratePublicBookingRequestMessage;
use App\Models\Appointment;
use App\Models\User;

/**
 * Builds WhatsApp deep links for booking confirmations.
 */
class WhatsAppService
{
    public function __construct(
        private GenerateBookingConfirmationMessage $generateMessage,
        private GeneratePublicBookingRequestMessage $generatePublicRequestMessage,
        private ClinicSettingsService $clinicSettings,
    ) {}

    /**
     * Open WhatsApp to the patient with a ready confirmation message.
     */
    public function patientConfirmationUrl(Appointment $appointment): string
    {
        $appointment->loadMissing(['patient']);

        $patientPhone = $appointment->patient?->phone
            ?? (string) config('clinic.whatsapp');

        return $this->url($patientPhone, $this->generateMessage->handle($appointment));
    }

    public function confirmationMessage(Appointment $appointment): string
    {
        return $this->generateMessage->handle($appointment);
    }

    /**
     * Open WhatsApp to the medical center with the patient's booking request details.
     */
    public function centerBookingRequestUrl(Appointment $appointment): ?string
    {
        $appointment->loadMissing('user');

        $number = $this->centerNumber($appointment->user);

        if ($number === '') {
            return null;
        }

        return $this->url($number, $this->generatePublicRequestMessage->handle($appointment));
    }

    public function centerNumber(?User $doctor = null): string
    {
        if ($doctor !== null) {
            $saved = trim((string) $this->clinicSettings->get($doctor)->whatsapp_number);

            if ($saved !== '') {
                return $saved;
            }
        }

        return (string) config('clinic.medical_center.whatsapp');
    }

    public function publicBookingRequestMessage(Appointment $appointment): string
    {
        return $this->generatePublicRequestMessage->handle($appointment);
    }

    public function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        while (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '963') || str_starts_with($digits, '966')) {
            return $digits;
        }

        if (str_starts_with($digits, '09') && (strlen($digits) === 10 || strlen($digits) === 11)) {
            return '963'.substr($digits, 1);
        }

        if (str_starts_with($digits, '05') && strlen($digits) === 10) {
            return '966'.substr($digits, 1);
        }

        if (str_starts_with($digits, '0')) {
            return '963'.substr($digits, 1);
        }

        return $digits;
    }

    public function url(string $number, string $message): string
    {
        $normalized = $this->normalizeNumber($number);

        return 'https://wa.me/'.$normalized.'?text='.rawurlencode($message);
    }
}
