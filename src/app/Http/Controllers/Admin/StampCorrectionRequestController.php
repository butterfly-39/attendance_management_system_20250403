<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakCorrectionRequest;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        // statusパラメータを取得（デフォルトは'承認待ち'）
        $status = $request->get('status', '承認待ち');
        // 管理者を除外したユーザーの打刻修正申請を取得
        $stampCorrectionRequests = StampCorrectionRequest::with('user')
            ->whereHas('user', function($query) {
                $query->where('is_admin', '!=', true);
            })
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp-correction.list', [
            'stampCorrections' => $stampCorrectionRequests
        ]);
    }

    public function showApprove($id)
    {
        // 打刻修正申請を取得
        $stampCorrection = StampCorrectionRequest::with(['user', 'attendance'])->findOrFail($id);

        // 関連する勤怠情報を取得
        $attendance = $stampCorrection->attendance;

        // 休憩時間修正申請を取得
        $breakCorrections = BreakCorrectionRequest::where('stamp_correction_request_id', $stampCorrection->id)
            ->get();

        return view('admin.stamp-correction.approve', [
            'attendance' => $attendance,
            'stampCorrection' => $stampCorrection,
            'breakCorrections' => $breakCorrections
        ]);
    }

    public function approve($id)
    {
        // トランザクション開始
        DB::beginTransaction();
        try {
            // 打刻修正申請を取得
            $stampCorrectionRequest = StampCorrectionRequest::with(['attendance', 'breakCorrectionRequests'])->findOrFail($id);

            // 勤怠情報を更新
            $attendance = $stampCorrectionRequest->attendance;
            $attendance->clock_in_time = $stampCorrectionRequest->clock_in_time;
            $attendance->clock_out_time = $stampCorrectionRequest->clock_out_time;
            $attendance->save();

            // 既存の休憩時間を削除
            $attendance->breakTimes()->delete();

            // 新しい休憩時間を追加
            foreach ($stampCorrectionRequest->breakCorrectionRequests as $breakCorrection) {
                $attendance->breakTimes()->create([
                    'break_start_time' => $breakCorrection->break_start_time,
                    'break_end_time' => $breakCorrection->break_end_time
                ]);
            }

            // 打刻修正申請のステータスを更新
            $stampCorrectionRequest->status = '承認済み';
            $stampCorrectionRequest->save();

            DB::commit();

            return redirect()
                ->route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $stampCorrectionRequest->id]);

        } catch (\Exception $exception) {
            DB::rollback();
            return redirect()
                ->route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $stampCorrectionRequest->id]);
        }
    }
}
