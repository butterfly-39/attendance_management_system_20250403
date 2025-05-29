<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakCorrectionRequest;

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
        // 勤怠情報を取得
        $attendance = Attendance::with(['user'])->findOrFail($id);

        // 承認待ちの申請を取得
        $stampCorrection = StampCorrectionRequest::where('attendance_id', $id)
            ->where('status', '承認待ち')
            ->firstOrFail();

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
        $stampCorrectionRequest = StampCorrectionRequest::find($id);
        $stampCorrectionRequest->status = '承認済み';
        $stampCorrectionRequest->save();
        return redirect()->route('admin.stamp_correction_request.list');
    }
}
