<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_duration_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
            'break_duration_minutes' => ['required', 'integer', 'in:0,5,10,15'],
            'lunch_enabled' => ['sometimes', 'boolean'],
            'lunch_start' => ['nullable', 'date_format:H:i', 'required_if:lunch_enabled,1'],
            'lunch_end' => ['nullable', 'date_format:H:i', 'required_if:lunch_enabled,1', 'after:lunch_start'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.weekday' => ['required', 'integer', 'between:0,6', 'distinct'],
            'days.*.is_open' => ['sometimes', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'appointment_duration_minutes' => 'مدة الموعد',
            'break_duration_minutes' => 'الاستراحة بين المواعيد',
            'lunch_enabled' => 'استراحة الغداء',
            'lunch_start' => 'بداية الغداء',
            'lunch_end' => 'نهاية الغداء',
            'days' => 'أيام العمل',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var array<int, array{weekday?: int, is_open?: mixed, start_time?: string|null, end_time?: string|null}> $days */
            $days = $this->input('days', []);

            foreach ($days as $index => $day) {
                $isOpen = (bool) ($day['is_open'] ?? false);

                if (! $isOpen) {
                    continue;
                }

                if (empty($day['start_time']) || empty($day['end_time'])) {
                    $validator->errors()->add(
                        "days.{$index}.start_time",
                        'يجب تحديد بداية ونهاية الدوام لليوم المفتوح.',
                    );

                    continue;
                }

                if ($day['end_time'] <= $day['start_time']) {
                    $validator->errors()->add(
                        "days.{$index}.end_time",
                        'يجب أن يكون وقت النهاية بعد وقت البداية.',
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $days = [];

        foreach ($this->input('days', []) as $key => $day) {
            if (! is_array($day)) {
                continue;
            }

            $days[] = [
                'weekday' => (int) ($day['weekday'] ?? 0),
                'is_open' => $this->boolean("days.{$key}.is_open"),
                'start_time' => $this->normalizeTimeInput($day['start_time'] ?? null),
                'end_time' => $this->normalizeTimeInput($day['end_time'] ?? null),
            ];
        }

        $this->merge([
            'lunch_enabled' => $this->boolean('lunch_enabled'),
            'lunch_start' => $this->normalizeTimeInput($this->input('lunch_start')),
            'lunch_end' => $this->normalizeTimeInput($this->input('lunch_end')),
            'days' => $days,
        ]);
    }

    /**
     * @return array{
     *     appointment_duration_minutes: int,
     *     break_duration_minutes: int,
     *     lunch_enabled: bool,
     *     lunch_start: string|null,
     *     lunch_end: string|null,
     *     days: list<array{weekday: int, is_open: bool, start_time: string|null, end_time: string|null}>
     * }
     */
    public function scheduleData(): array
    {
        /** @var array{
         *     appointment_duration_minutes: int|string,
         *     break_duration_minutes: int|string,
         *     lunch_enabled: bool,
         *     lunch_start?: string|null,
         *     lunch_end?: string|null,
         *     days: list<array{weekday: int, is_open: bool, start_time?: string|null, end_time?: string|null}>
         * } $validated
         */
        $validated = $this->validated();

        return [
            'appointment_duration_minutes' => (int) $validated['appointment_duration_minutes'],
            'break_duration_minutes' => (int) $validated['break_duration_minutes'],
            'lunch_enabled' => (bool) $validated['lunch_enabled'],
            'lunch_start' => $validated['lunch_start'] ?? null,
            'lunch_end' => $validated['lunch_end'] ?? null,
            'days' => collect($validated['days'])
                ->map(fn (array $day): array => [
                    'weekday' => (int) $day['weekday'],
                    'is_open' => (bool) $day['is_open'],
                    'start_time' => $day['is_open'] ? ($day['start_time'] ?? null) : null,
                    'end_time' => $day['is_open'] ? ($day['end_time'] ?? null) : null,
                ])
                ->values()
                ->all(),
        ];
    }

    private function normalizeTimeInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return substr($value, 0, 5);
        }

        return $value;
    }
}
