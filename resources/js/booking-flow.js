export function registerBookingFlow(Alpine) {
    Alpine.data('bookingFlow', (weeks, appointmentTypes, options = {}) => ({
        weeks,
        appointmentTypes,
        currentStep: 1,
        wizard: options.wizard === true,
        wizardSteps: ['service', 'schedule', 'patient', 'review'],
        selectedWeek: 'this',
        selectedDate: '',
        selectedTime: '',
        selectedTimeLabel: '',
        patientName: options.initialName ?? '',
        patientPhone: options.initialPhone ?? '',
        appointmentTypeId: options.initialAppointmentTypeId
            ? String(options.initialAppointmentTypeId)
            : '',
        status: options.defaultStatus ?? '',
        submitting: false,
        backUrl: options.backUrl ?? null,

        init() {
            if (
                ! this.appointmentTypeId
                && Array.isArray(this.appointmentTypes)
                && this.appointmentTypes.length === 1
            ) {
                this.appointmentTypeId = String(this.appointmentTypes[0].id);
            }

            if (this.wizard && this.appointmentTypeId && this.appointmentTypes.length === 1) {
                this.currentStep = 2;
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

        get globalStep() {
            return 2 + this.currentStep;
        },

        get stepTitle() {
            const titles = {
                1: 'اختر الخدمة',
                2: 'اختر اليوم والوقت',
                3: 'بيانات المريض',
                4: 'مراجعة وتأكيد الحجز',
            };

            return titles[this.currentStep] ?? '';
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
                missing.push('رقم واتساب بصيغة صحيحة (مثال: 0999123456 أو +963999123456)');
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

        get canGoNext() {
            if (this.submitting) {
                return false;
            }

            switch (this.currentStep) {
                case 1:
                    return Boolean(this.appointmentTypeId);
                case 2:
                    return Boolean(this.selectedDate && this.selectedTime);
                case 3:
                    return this.patientName.trim().length >= 2 && this.phoneValid;
                case 4:
                    return this.canSubmit;
                default:
                    return false;
            }
        },

        nextStep() {
            if (! this.canGoNext || this.currentStep >= 4) {
                return;
            }

            this.currentStep += 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep -= 1;
                window.scrollTo({ top: 0, behavior: 'smooth' });

                return;
            }

            if (this.backUrl) {
                window.location.href = this.backUrl;
            }
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

        onSubmit(event) {
            this.formatPhone();

            if (this.wizard && this.currentStep < 4) {
                event.preventDefault();

                if (this.canGoNext) {
                    this.nextStep();
                }

                return;
            }

            if (! this.canSubmit) {
                event.preventDefault();

                return;
            }

            this.patientPhone = this.normalizedPhone;
            this.submitting = true;
        },
    }));

    Alpine.data('bookingSelection', (options = {}) => ({
        selected: options.initial ?? null,
        urls: options.urls ?? {},
        backUrl: options.backUrl ?? null,

        select(id) {
            this.selected = String(id);
        },

        isSelected(id) {
            return this.selected !== null && String(this.selected) === String(id);
        },

        goNext() {
            if (! this.selected) {
                return;
            }

            const url = this.urls[this.selected] ?? this.urls[String(this.selected)] ?? null;

            if (url) {
                window.location.href = url;
            }
        },

        goBack() {
            if (this.backUrl) {
                window.location.href = this.backUrl;
            }
        },
    }));
}
