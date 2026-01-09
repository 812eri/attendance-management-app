<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        return view('stamp_correction_request.index');
    }

    public function store(Request $request)
    {
        
    }
}
