<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        return view('attendance.index', compact('attendance'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        $currentDateTime = Carbon::now();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        $action = $request->input('action');
        switch ($action) {
            case 'clock_in':
                if (!$attendance) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $today->format('Y-m-d'),
                        'clock_in_time' => $currentDateTime,
                        'status' => '出勤中'
                    ]);
                }
                break;

            case 'break_start':
                if ($attendance && $attendance->status === '出勤中') {
                    $attendance->breakTimes()->create([
                        'break_start_time' => $currentDateTime
                    ]);
                    $attendance->update(['status' => '休憩中']);
                }
                break;

            case 'break_end':
                if ($attendance && $attendance->status === '休憩中') {
                    $latestBreak = $attendance->breakTimes()
                        ->whereNull('break_end_time')
                        ->latest()
                        ->first();
                    
                    if ($latestBreak) {
                        $latestBreak->update([
                            'break_end_time' => $currentDateTime
                        ]);
                        $attendance->update(['status' => '出勤中']);
                    }
                }
                break;

            case 'clock_out':
                if ($attendance && $attendance->status === '出勤中') {
                    $attendance->update([
                        'clock_out_time' => $currentDateTime,
                        'status' => '退勤済'
                    ]);
                }
                break;
        }

        return redirect()->back();
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        
        // 表示する月を設定（デフォルトは現在の月）
        $currentDate = $request->input('date') 
            ? Carbon::parse($request->input('date')) 
            : Carbon::now();
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $currentDate
        ]);
    }

    // 勤怠詳細画面用のメソッドを追加
    public function show($id)
    {
        $attendance = Auth::user()->attendances()->findOrFail($id);
        return view('attendance.show', compact('attendance'));
    }
}
