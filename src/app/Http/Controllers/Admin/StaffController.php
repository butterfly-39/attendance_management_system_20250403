<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use League\Csv\Writer;
class StaffController extends Controller
{
    public function list()
    {
        // is_adminがfalseのユーザーのみを取得
        $staff = User::where('is_admin', false)
            ->with(['attendances' => function($query) {
                // 今月の勤怠データを取得
                $query->whereMonth('date', Carbon::now()->month)
                      ->whereYear('date', Carbon::now()->year);
            }])
            ->get();

        return view('admin.staff.list', [
            'staff' => $staff
        ]);
    }

    /**
     * スタッフの勤怠一覧を表示
     *
     * @param int $id スタッフのID
     * @return \Illuminate\View\View
     */
    public function attendanceList($id, Request $request)
    {
        // リクエストから日付を取得、なければ現在の日付を使用
        $currentDate = $request->has('date')
            ? Carbon::createFromFormat('Y-m', $request->date)
            : Carbon::now();

        $staff = User::with(['attendances' => function($query) use ($currentDate) {
            $query->whereYear('date', $currentDate->year)
                ->whereMonth('date', $currentDate->month)
                ->orderBy('date', 'desc');
        }])->findOrFail($id);

        $attendances = $staff->attendances;

        return view('admin.staff.attendance-list', [
            'staff' => $staff,
            'currentDate' => $currentDate,
            'attendances' => $attendances,
        ]);
    }

public function export($id, Request $request)
{
    $staff = User::findOrFail($id);
    $date = Carbon::parse($request->query('date', now()->format('Y-m')));

    $attendances = Attendance::where('user_id', $id)
        ->whereYear('date', $date->year)
        ->whereMonth('date', $date->month)
        ->orderBy('date')
        ->get();

    $csv = Writer::createFromString('');

    // ヘッダー行を追加
    $csv->insertOne([
        '日付',
        '曜日',
        '出勤時間',
        '退勤時間',
        '休憩時間',
        '勤務時間'
    ]);

    // データ行を追加
    foreach ($attendances as $attendance) {
        $csv->insertOne([
            Carbon::parse($attendance->date)->format('Y/m/d'),
            Carbon::parse($attendance->date)->isoFormat('ddd'),
            $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '',
            $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '',
            $attendance->total_break_time ?? '',
            $attendance->total_work_time ?? ''
        ]);
    }

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $staff->name . '_勤怠記録_' . $date->format('Y年m月') . '.csv"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    return response($csv->toString())
        ->withHeaders($headers);
}
}
