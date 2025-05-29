<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrectionRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        // URLパラメータから日付を取得、なければ現在の日付を使用
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        // 指定された日付の勤怠データを取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $currentDate->format('Y-m-d'))
            ->get();

        return view('admin.attendance.list', [
            'currentDate' => $currentDate,
            'attendances' => $attendances
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);
        
        // 承認待ちの修正リクエストを取得
        $stampCorrection = StampCorrectionRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();
        
        $breakCorrections = [];
        if ($stampCorrection) {
            $breakCorrections = BreakCorrectionRequest::where('stamp_correction_id', $stampCorrection->id)->get();
        }

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'pendingRequest' => !is_null($stampCorrection),
            'stampCorrection' => $stampCorrection,
            'breakCorrections' => $breakCorrections
        ]);
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $date = Carbon::parse($attendance->date)->format('Y-m-d');

            // Attendanceテーブルを直接更新
            $attendance->update([
                'clock_in_time' => $date . ' ' . $request->clock_in_time,
                'clock_out_time' => $date . ' ' . $request->clock_out_time,
            ]);

            // 既存の休憩時間レコードを取得
            $existingBreaks = $attendance->breakTimes()->orderBy('id')->get();

            // 休憩時間の更新・作成
            if ($request->break_start_time) {
                foreach ($request->break_start_time as $key => $start_time) {
                    // 空の休憩時間エントリーをスキップ
                    if (!$start_time || !isset($request->break_end_time[$key]) || !$request->break_end_time[$key]) {
                        continue;
                    }

                    $breakData = [
                        'attendance_id' => $attendance->id,
                        'break_start_time' => $date . ' ' . $start_time,
                        'break_end_time' => $date . ' ' . $request->break_end_time[$key],
                    ];

                    // 既存のレコードがあれば更新、なければ作成
                    if (isset($existingBreaks[$key])) {
                        $existingBreaks[$key]->update($breakData);
                    } else {
                        BreakTime::create($breakData);
                    }
                }
            }

            // リクエストで送信された休憩時間数より多い既存レコードは削除
            if ($request->break_start_time) {
                $requestBreakCount = count(array_filter($request->break_start_time));
                if ($existingBreaks->count() > $requestBreakCount) {
                    $attendance->breakTimes()->skip($requestBreakCount)->take($existingBreaks->count() - $requestBreakCount)->delete();
                }
            } else {
                // 休憩時間がリクエストにない場合は全て削除
                $attendance->breakTimes()->delete();
            }
        });

        return redirect()->route('admin.attendance.show', ['id' => $id]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/admin/login');
    }
}
