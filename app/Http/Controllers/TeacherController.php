<?php

namespace App\Http\Controllers;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Students;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /// TA ROLE
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function subject()
    {
        $subjects = Subjects::all();
        return view('layouts.teacher.subject', compact('subjects'));
    }

    public function subjectDetail()
    {
        $subjects = Subjects::all();
        $teachers = Teachers::all();
        $students = Students::all();
        return view('layouts.teacher.subjectDetail', compact('subjects'), compact('teachers'), compact('students'));
    }
    public function taDetail()
    {
        return view('layouts.teacher.taDetail');
    }


}
