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

    public function getRestSumAttribute()
    {
        return gmdate('H:i', $this->getTotalRestSeconds());
    }

    public function getWorkTimeAttribute()
    {
        if (!$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        $totalSeconds = $start->diffInSeconds($end);

        $restSeconds = $this->getTotalRestSeconds();

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