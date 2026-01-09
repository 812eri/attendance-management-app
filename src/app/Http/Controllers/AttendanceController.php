<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance.index');
    }

    public function list()
    {
        return view('attendance.list');
    }

    public function show($id)
    {
        return view('attendance.show');
    }
}
