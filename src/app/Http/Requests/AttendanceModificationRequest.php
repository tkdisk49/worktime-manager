<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceModificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'new_clock_in' => 'required|date_format:H:i',
            'new_clock_out' => 'required|date_format:H:i',
            'new_remarks' => 'required|string|max:255',
            'existing_breaks.*.start' => 'nullable|date_format:H:i',
            'existing_breaks.*.end' => 'nullable|date_format:H:i',
            'new_break_start' => 'nullable|date_format:H:i',
            'new_break_end' => 'nullable|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'new_remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->filled('new_clock_in') ? Carbon::parse($this->input('new_clock_in')) : null;
            $clockOut = $this->filled('new_clock_out') ? Carbon::parse($this->input('new_clock_out')) : null;

            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $validator->errors()->add('new_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($clockIn && $clockOut) {
                foreach ($this->input('existing_breaks', []) as $index => $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $start = Carbon::parse($break['start']);
                        $end = Carbon::parse($break['end']);

                        if ($start->gt($end)) {
                            $validator->errors()->add("existing_breaks.$index.start", '休憩開始時間もしくは休憩終了時間が不適切な値です');
                        }

                        if ($start->lt($clockIn) || $end->gt($clockOut)) {
                            $validator->errors()->add("existing_breaks.$index.start", '休憩時間が勤務時間外です');
                        }
                    }
                }

                if ($this->filled('new_break_start') && $this->filled('new_break_end')) {
                    $start = Carbon::parse($this->input('new_break_start'));
                    $end = Carbon::parse($this->input('new_break_end'));

                    if ($start->gt($end)) {
                        $validator->errors()->add("new_break_start", '休憩開始時間もしくは休憩終了時間が不適切な値です');
                    }

                    if ($start->lt($clockIn) || $end->gt($clockOut)) {
                        $validator->errors()->add('new_break_start', '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
