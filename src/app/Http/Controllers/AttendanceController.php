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
use App\Models\BreakCorrectionRequest;

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
        
        // 承認待ちの申請があるかチェック
        $pendingRequest = StampCorrectionRequest::where('attendance_id', $id)
            ->where('status', '承認待ち')
            ->exists();

        return view('attendance.show', compact('attendance', 'breakTimes', 'pendingRequest'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $date = Carbon::parse($attendance->date)->format('Y-m-d');

            // StampCorrectionRequestを作成
            $stampCorrectionRequest = StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'clock_in_time' => $date . ' ' . $request->clock_in_time,
                'clock_out_time' => $date . ' ' . $request->clock_out_time,
                'status' => '承認待ち',
                'note' => $request->note
            ]);

            // 休憩時間の修正申請を作成
            if ($request->break_start_time) {
                foreach ($request->break_start_time as $key => $start_time) {
                    // 空の休憩時間エントリーをスキップ
                    if (!$start_time || !$request->break_end_time[$key]) {
                        continue;
                    }

                    // 休憩時間の修正申請を作成
                    BreakCorrectionRequest::create([
                        'stamp_correction_request_id' => $stampCorrectionRequest->id,
                        'break_start_time' => $date . ' ' . $start_time,
                        'break_end_time' => $date . ' ' . $request->break_end_time[$key]
                    ]);
                }
            }
        });

        return redirect()->route('attendance.index');
    }
}
