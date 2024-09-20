<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requests;


class RequestsController extends Controller
{
    public function showCourseTas()
    {
        $requests = Requests::with(['courseTas.course.subjects', 'courseTas.student'])->get();
        return view('layouts.ta.statusRequest', compact('requests'));
    }

    // public function showCourseTasAdmin()
    // {
    //     $requests = Requests::with(['courseTas.course.subjects', 'courseTas.student'])->get();
    //     return view('layouts.adminHome', compact('requests'));
    // }
}
