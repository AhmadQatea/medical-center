<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Support\TimeFormat;

class GenerateBookingConfirmationMessage
{
    public function handle(Appointment $appointment): string
    {
        $appointment->loadMissing(['patient', 'user.clinicSetting', 'appointmentType']);

        $patientName = $appointment->patient?->name ?? 'عزيزي المريض';
        $clinicName = $appointment->user?->clinicSetting?->clinic_name ?? config('clinic.name');
        $dateLabel = $appointment->date?->locale('ar')->translatedFormat('l j F Y') ?? '';
        $timeLabel = TimeFormat::arabic((string) $appointment->start_time);
        $typeLabel = $appointment->typeLabel();

        return implode("\n", [
            "السلام عليكم {$patientName}",
            '',
            "تم تأكيد موعدكم في {$clinicName}.",
            '',
            '📅 التاريخ:',
            $dateLabel,
            '',
            '🕐 الوقت:',
            $timeLabel,
            '',
            '🦷 نوع الموعد:',
            $typeLabel,
            '',
            'يرجى الحضور قبل الموعد بعشر دقائق.',
            '',
            'إذا تعذر الحضور يرجى التواصل معنا.',
            '',
            'شكراً لكم.',
        ]);
    }
}
