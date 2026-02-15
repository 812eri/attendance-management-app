<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStampCorrectionRequest extends FormRequest
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
            'attendance_id' => 'required|exists:attendances,id',
            'new_start_time' => 'required',
            'new_end_time' => 'required|after:new_start_time',
            'new_remarks' => 'required',

            'new_break_starts' => 'nullable|array',
            'new_break_ends' => 'nullable|array',

            'new_break_starts.*' => 'nullable|required_with:new_break_ends.*|after:new_start_time|before:new_end_time',
            'new_break_ends.*' => 'nullable|required_with:new_break_starts.*|after:new_break_starts.*|before:new_end_time',
        ];
    }

    public function attributes()
    {
        return [
            'new_start_time' => '出勤時間',
            'new_end_time' => '退勤時間',
            'new_remarks' => '備考',
        ];
    }

    public function messages()
    {
        return [
            'new_remarks.required' => '備考を記入してください',

            'new_end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'new_break_starts.*.after' => '休憩時間が不適切な値です',
            'new_break_starts.*.before' => '休憩時間が不適切な値です',
            'new_break_ends.*.after' => '休憩時間が不適切な値です',
            'new_break_ends.*.before' => '休憩時間が不適切な値です',

            'new_break_starts.*.required_with' => '休憩時間が不適切な値です',
            'new_break_ends.*.required_with' => '休憩時間が不適切な値です',
        ];
    }
}
