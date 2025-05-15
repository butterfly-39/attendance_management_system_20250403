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

            // StampCorrectionRequestを作成
            $stampCorrectionRequest = StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'clock_in_time' => $date . ' ' . $request->clock_in_time,
                'clock_out_time' => $date . ' ' . $request->clock_out_time,
                'status' => '承認待ち',
                'note' => $request->note
            ]);

            // 既存の休憩時間と比較して変更があるかチェック
            if ($request->break_start_time) {
                $existingBreaks = $attendance->breakTimes->toArray();

                foreach ($request->break_start_time as $key => $start_time) {
                    // 空の休憩時間エントリーをスキップ
                    if (!$start_time || !isset($request->break_end_time[$key]) || !$request->break_end_time[$key]) {
                        continue;
                    }

                    $newBreakStart = $date . ' ' . $start_time;
                    $newBreakEnd = $date . ' ' . $request->break_end_time[$key];

                    // 既存の休憩時間と異なる場合のみ修正申請を作成
                    $isModified = true;
                    if (isset($existingBreaks[$key])) {
                        $existingStart = Carbon::parse($existingBreaks[$key]['break_start_time'])->format('Y-m-d H:i');
                        $existingEnd = Carbon::parse($existingBreaks[$key]['break_end_time'])->format('Y-m-d H:i');

                        if ($existingStart === $newBreakStart && $existingEnd === $newBreakEnd) {
                            $isModified = false;
                        }
                    }

                    if ($isModified) {
                        BreakCorrectionRequest::create([
                            'stamp_correction_request_id' => $stampCorrectionRequest->id,
                            'break_start_time' => $newBreakStart,
                            'break_end_time' => $newBreakEnd
                        ]);
                    }
                }
            }
        });

        // idパラメータを追加してリダイレクト
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
