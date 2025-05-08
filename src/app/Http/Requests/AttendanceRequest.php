<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_start_time.*' => 'nullable|date_format:H:i|after_or_equal:clock_in_time|before:clock_out_time',
            'break_end_time.*' => 'nullable|date_format:H:i|after:break_start_time.*|before_or_equal:clock_out_time',
            'note' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_out_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start_time.*.after_or_equal' => '休憩時間が勤務時間外です',
            'break_start_time.*.before' => '休憩時間が勤務時間外です',
            'break_end_time.*.after' => '休憩時間が勤務時間外です',
            'break_end_time.*.before_or_equal' => '休憩時間が勤務時間外です',
            'note.required' => '備考を記入してください',
        ];
    }
}
