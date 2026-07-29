export function registerBookingFlow(Alpine) {
    Alpine.data('bookingFlow', (weeks, appointmentTypes, options = {}) => ({
        weeks,
        appointmentTypes,
        selectedWeek: 'this',
        selectedDate: '',
        selectedTime: '',
        selectedTimeLabel: '',
        patientName: '',
        patientPhone: '',
        appointmentTypeId: '',
        status: options.defaultStatus ?? '',
        submitting: false,

        init() {
            if (Array.isArray(this.appointmentTypes) && this.appointmentTypes.length === 1) {
                this.appointmentTypeId = String(this.appointmentTypes[0].id);
            }
        },

        get currentDays() {
            if (this.selectedWeek === 'next') {
                return this.weeks.next_week ?? [];
            }

            return this.weeks.this_week ?? [];
        },

        get currentTimes() {
            const day = this.currentDays.find((item) => item.date === this.selectedDate);

            return day?.times ?? [];
        },

        get summaryDay() {
            const day = this.currentDays.find((item) => item.date === this.selectedDate);

            if (! day) {
                return '';
            }

            return `${day.weekday_label} — ${day.day_label}`;
        },

        get appointmentTypeLabel() {
            if (! this.appointmentTypeId) {
                return '';
            }

            const type = this.appointmentTypes.find(
                (item) => String(item.id) === String(this.appointmentTypeId),
            );

            return type?.name ?? '';
        },

        get normalizedPhone() {
            return this.normalizePhone(this.patientPhone);
        },

        get phoneValid() {
            return /^\+9639\d{8}$/.test(this.normalizedPhone);
        },

        get missingFields() {
            const missing = [];

            if (! this.selectedDate || ! this.selectedTime) {
                missing.push('اليوم والوقت');
            }

            if (this.patientName.trim().length < 2) {
                missing.push('الاسم الكامل');
            }

            if (! this.phoneValid) {
                missing.push('رقم واتساب بصيغة صحيحة (مثال: 0959422413 أو +963959422413)');
            }

            if (! this.appointmentTypeId) {
                missing.push('نوع الموعد');
            }

            if (options.requireStatus && ! this.status) {
                missing.push('الحالة');
            }

            return missing;
        },

        get canSubmit() {
            return this.missingFields.length === 0 && ! this.submitting;
        },

        normalizePhone(raw) {
            const value = String(raw ?? '').trim();

            if (value === '') {
                return '';
            }

            let digits = value.replace(/\D+/g, '');

            while (digits.startsWith('00')) {
                digits = digits.slice(2);
            }

            if (digits.startsWith('963') && digits.length === 12) {
                return `+${digits}`;
            }

            if (digits.startsWith('09') && digits.length === 10) {
                return `+963${digits.slice(1)}`;
            }

            if (digits.startsWith('9') && digits.length === 9) {
                return `+963${digits}`;
            }

            if (value.startsWith('+') && /^\+9639\d{8}$/.test(value.replace(/\s+/g, ''))) {
                return value.replace(/\s+/g, '');
            }

            return value.replace(/\s+/g, '');
        },

        formatPhone() {
            const normalized = this.normalizePhone(this.patientPhone);

            if (/^\+9639\d{8}$/.test(normalized)) {
                this.patientPhone = normalized;
            }
        },

        selectDay(day) {
            this.selectedDate = day.date;
            this.selectedTime = '';
            this.selectedTimeLabel = '';
        },

        selectTime(slot) {
            this.selectedTime = slot.value;
            this.selectedTimeLabel = slot.label;
        },

        onSubmit() {
            this.formatPhone();

            if (! this.canSubmit) {
                return false;
            }

            this.patientPhone = this.normalizedPhone;
            this.submitting = true;

            return true;
        },
    }));
}
