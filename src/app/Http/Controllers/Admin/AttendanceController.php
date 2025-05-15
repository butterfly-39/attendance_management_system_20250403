<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

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
        
        return view('admin.attendance.show', [
            'attendance' => $attendance
        ]);
    }
}