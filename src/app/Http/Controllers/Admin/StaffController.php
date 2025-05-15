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

    
}
