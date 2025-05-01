<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceRequest;
use App\Models\StampCorrectionRequest;

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

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);
        $breakTimes = $attendance->breakTimes()->first();

        return view('attendance.show', compact('attendance', 'breakTimes'));
    }

    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        DB::transaction(function () use ($request, $attendance) {
            // 勤怠情報を更新
            $attendance->update([
                'clock_in_time' => $request->clock_in_time,
                'clock_out_time' => $request->clock_out_time,
                'note' => $request->note,
                'status' => 'pending', // 承認待ちステータス
            ]);

            // 既存の休憩時間を削除
            $attendance->breakTimes()->delete();

            // 新しい休憩時間を登録
            foreach ($request->break_start_time as $key => $start_time) {
                if ($start_time && $request->break_end_time[$key]) {
                    $attendance->breakTimes()->create([
                        'break_start_time' => $start_time,
                        'break_end_time' => $request->break_end_time[$key],
                    ]);
                }
            }

            // 修正申請を作成
            StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('attendance.index');
    }
}
