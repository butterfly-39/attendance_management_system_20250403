<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        // URLパラメータから日付を取得、なければ現在の日付を使用
        $date = $request->query('date', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($date);

        // とりあえず空の配列を設定（後で実際の勤怠データ取得処理に置き換え）
        $attendances = [];

        return view('admin.attendance.list', [
            'currentDate' => $currentDate,
            'attendances' => $attendances
        ]);
    }
}