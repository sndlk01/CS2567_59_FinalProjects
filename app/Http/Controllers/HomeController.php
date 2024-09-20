<?php

namespace App\Http\Controllers;

use App\Models\Requests;
use Illuminate\Http\Request;
use App\Models\Announce;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the admin dashboard with announcements.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        // $announces = Announce::orderBy('created_at', 'desc')->get();
        // return view('adminHome', compact('announces'));
        $requests = Requests::with(['courseTas.course.subjects', 'courseTas.student'])->get();
        return view('adminHome', compact('requests'));
    }


    /**
     * Show the teacher's dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function teacherHome()
    {
        return view('teacherHome');
    }
}
