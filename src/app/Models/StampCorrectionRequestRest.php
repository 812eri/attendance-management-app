<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestRest extends Model
{
    use HasFactory;

    protected $fillable = ['stamp_correction_request_id', 'new_break_start', 'new_break_end'];

    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}
