<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function list()
    {
        return view('stamp-correction.list');
    }
}
