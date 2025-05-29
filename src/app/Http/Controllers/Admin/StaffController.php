<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

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
}
