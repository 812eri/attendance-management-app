<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceUpdateRequest extends FormRequest
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
            'start_time' => 'required',
            'end_time' => 'required',
            'remarks' => 'required',
            'rests.*.start_time' => 'nullable',
            'rests.*.end_time' => 'nullable',
            'new_rest.start_time' => 'nullable',
            'new_rest.end_time' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'remarks.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $parseTime = function($timeStr) {
                return $timeStr ? Carbon::createFromFormat('H:i', $timeStr) : null;
            };

            $workStart = $parseTime($data['start_time']);
            $workEnd = $parseTime($data['end_time']);

            if ($workStart && $workEnd && $workStart->gt($workEnd)) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $checkRest = function($restStartStr, $restEndStr, $indexKey) use ($validator, $workStart, $workEnd, $parseTime) {
                $restStart = $parseTime($restStartStr);
                $restEnd = $parseTime($restEndStr);

                if (!$restStart || !$restEnd) return;

                if ($workStart && $workEnd) {
                    if ($restStart->lt($workStart) || $restEnd->gt($workEnd)) {
                        $validator->errors()->add($indexKey, '休憩時間が不適切な値です');
                    }
                }

                if ($restEnd->lt($restStart)) {
                    $validator->errors()->add($indexKey, '休憩時間が不適切な値です');
                }
            };

            if (isset($data['rests'])) {
                foreach ($data['rests'] as $id => $rest) {
                    $checkRest($rest['start_time'], $rest['end_time'], "rests.{$id}.start_time");
                }
            }

            if (isset($data['new_rest'])) {
                $checkRest($data['new_rest']['start_time'], $data['new_rest']['end_time'], 'new_rest.start_time');
            }
        });
    }
}
