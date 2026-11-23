/**
 * Booking form: month switch, day + slot selection and the live summary.
 * The form also works without JavaScript (plain radio inputs), this only improves the experience.
 */
export function bookingForm({ month = 0, date = null, slot = null, course = null, courses = {} } = {}) {
    return {
        month,
        date,
        slot,
        course,
        courses,

        selectDay(day) {
            if (this.date !== day) {
                this.slot = null;
            }
            this.date = day;
            this.$nextTick(() => this.$refs.slots?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
        },

        get selectedCourse() {
            return this.courses[this.course] ?? null;
        },

        get dateLabel() {
            if (!this.date) {
                return null;
            }

            const label = new Date(`${this.date}T12:00:00`).toLocaleDateString('sk-SK', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });

            return label.charAt(0).toUpperCase() + label.slice(1);
        },
    };
}
