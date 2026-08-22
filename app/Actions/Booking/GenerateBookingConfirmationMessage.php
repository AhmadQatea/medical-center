<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Support\TimeFormat;

class GenerateBookingConfirmationMessage
{
    public function handle(Appointment $appointment): string
    {
        $appointment->loadMissing(['patient', 'user.clinicSetting', 'appointmentType', 'clinic']);

        $patientName = $appointment->patient?->name ?? 'عزيزي المريض';
        $medicalCenterName = (string) config('clinic.medical_center.name');
        $clinicName = $appointment->clinic?->name
            ?? $appointment->user?->clinicSetting?->clinic_name
            ?? '—';
        $doctorName = $appointment->user?->name ?? '—';
        $dateLabel = $appointment->date?->locale('ar')->translatedFormat('l j F Y') ?? '';
        $timeLabel = TimeFormat::arabic((string) $appointment->start_time);
        $typeLabel = $appointment->typeLabel();

        return implode("\n", [
            'طلب موعد جديد',
            '',
            "المركز: {$medicalCenterName}",
            "العيادة: {$clinicName}",
            "الطبيب: {$doctorName}",
            '',
            '📅 التاريخ:',
            $dateLabel,
            '',
            '🕐 الوقت:',
            $timeLabel,
            '',
            '📋 نوع الموعد:',
            $typeLabel,
            '',
            "👤 المريض: {$patientName}",
            '📱 الهاتف: '.($appointment->patient?->phone ?? '—'),
            '',
            'يرجى الحضور قبل الموعد بعشر دقائق.',
            '',
            'إذا تعذر الحضور يرجى التواصل معنا.',
            '',
            'شكراً لكم.',
        ]);
    }
}
