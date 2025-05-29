<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\User;

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
}
