<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Support\TimeFormat;

/**
 * Builds a simple WhatsApp message for a patient to send the center after booking.
 */
class GeneratePublicBookingRequestMessage
{
    public function handle(Appointment $appointment): string
    {
        $appointment->loadMissing(['patient', 'user.clinicSetting', 'appointmentType', 'clinic']);

        $medicalCenterName = (string) config('clinic.medical_center.name');
        $clinicName = $appointment->clinic?->name
            ?? $appointment->user?->clinicSetting?->clinic_name
            ?? '—';
        $doctorName = $appointment->user?->name ?? '—';
        $patientName = $appointment->patient?->name ?? '—';
        $phone = $appointment->patient?->phone ?? '—';
        $dateLabel = $appointment->date?->locale('ar')->translatedFormat('l j F Y') ?? '—';
        $timeLabel = TimeFormat::arabic((string) $appointment->start_time);
        $typeLabel = $appointment->typeLabel();

        return implode("\n", [
            'طلب حجز موعد',
            '',
            "المركز: {$medicalCenterName}",
            "العيادة: {$clinicName}",
            "الطبيب: {$doctorName}",
            "المريض: {$patientName}",
            "الهاتف: {$phone}",
            "التاريخ: {$dateLabel}",
            "الوقت: {$timeLabel}",
            "نوع الموعد: {$typeLabel}",
        ]);
    }
}
