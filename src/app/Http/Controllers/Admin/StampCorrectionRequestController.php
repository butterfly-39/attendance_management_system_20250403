<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function list()
    {
        $stampCorrectionRequests = StampCorrectionRequest::all();
        return view('admin.stamp-correction.list', compact('stampCorrectionRequests'));
    }
}
