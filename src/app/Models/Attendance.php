<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    // --- 休憩時間の合計（秒数）を計算する共通メソッド（計算用） ---
    // これを作っておくと、他でも使い回せます
    public function getTotalRestSeconds()
    {
        $totalRestSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = Carbon::parse($rest->start_time);
                $end = Carbon::parse($rest->end_time);
                $totalRestSeconds += $start->diffInSeconds($end);
            }
        }
        return $totalRestSeconds;
    }

    // --- 表示用アクセサ：休憩合計 ---
    public function getRestSumAttribute()
    {
        // 共通メソッドを使って秒数を取得し、フォーマットするだけ
        return gmdate('H:i', $this->getTotalRestSeconds());
    }

    // --- 表示用アクセサ：勤務時間 ---
    public function getWorkTimeAttribute()
    {
        // 退勤していない場合は null を返す（Blade側で空白になる）
        if (!$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // 拘束時間（秒）
        $totalSeconds = $start->diffInSeconds($end);

        // 休憩時間（秒）を共通メソッドから取得
        $restSeconds = $this->getTotalRestSeconds();

        // 実働時間 = 拘束時間 - 休憩
        $workSeconds = $totalSeconds - $restSeconds;

        return gmdate('H:i', $workSeconds);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function isPending()
    {
        return $this->stampCorrectionRequests()->where('status', 'pending')->exists();
    }
}