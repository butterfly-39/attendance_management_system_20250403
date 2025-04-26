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
        'clock_in_time',
        'clock_out_time',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getTotalBreakTimeAttribute()
    {
        $totalBreakMinutes = 0;
        
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->break_start_time && $breakTime->break_end_time) {
                $start = Carbon::parse($breakTime->break_start_time);
                $end = Carbon::parse($breakTime->break_end_time);
                $totalBreakMinutes += $end->diffInMinutes($start);
            }
        }
        
        if ($totalBreakMinutes === 0) {
            return null;
        }
        
        $hours = floor($totalBreakMinutes / 60);
        $minutes = $totalBreakMinutes % 60;
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getTotalWorkTimeAttribute()
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return null;
        }

        $start = Carbon::parse($this->clock_in_time);
        $end = Carbon::parse($this->clock_out_time);
        
        // 総勤務時間（分）を計算
        $totalMinutes = $end->diffInMinutes($start);
        
        // 休憩時間（分）を計算
        $totalBreakMinutes = 0;
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->break_start_time && $breakTime->break_end_time) {
                $breakStart = Carbon::parse($breakTime->break_start_time);
                $breakEnd = Carbon::parse($breakTime->break_end_time);
                $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
            }
        }
        
        // 実労働時間を計算
        $actualWorkMinutes = $totalMinutes - $totalBreakMinutes;
        
        if ($actualWorkMinutes <= 0) {
            return null;
        }
        
        $hours = floor($actualWorkMinutes / 60);
        $minutes = $actualWorkMinutes % 60;
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }

}
