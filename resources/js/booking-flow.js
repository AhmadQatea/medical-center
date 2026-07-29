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

        get canSubmit() {
            const phoneValid = /^\+9639\d{8}$/.test(this.patientPhone.trim());
            const typeValid = Boolean(this.appointmentTypeId);
            const statusValid = ! options.requireStatus || this.status;

            return Boolean(
                this.selectedDate
                && this.selectedTime
                && this.patientName.trim().length > 1
                && phoneValid
                && typeValid
                && statusValid
                && ! this.submitting,
            );
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
            if (! this.canSubmit) {
                return false;
            }

            this.submitting = true;

            return true;
        },
    }));
}
