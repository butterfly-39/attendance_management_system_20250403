<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->get('status', '承認待ち');

        $stampCorrections = StampCorrectionRequest::where('status', $status)
            ->where('user_id', Auth::id())
            ->get();

        return view('stamp-correction.list', compact('stampCorrections'));
    }

    public function show($id)
    {
        $stampCorrection = StampCorrectionRequest::find($id);
        return view('stamp-correction.show', compact('stampCorrection'));
    }
}
