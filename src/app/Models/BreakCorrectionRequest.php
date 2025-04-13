<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stamp_correction_request_id',
        'break_start_time',
        'break_end_time',
        'note',
        'status',
    ];
}
