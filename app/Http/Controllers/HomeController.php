<?php

namespace App\Http\Controllers;

use App\Models\Requests;
use Illuminate\Http\Request;
use App\Models\Announce;
use App\Models\CourseTas;
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
        

        $requests = CourseTas::with([
            'student',
            'course.subjects',
            'courseTaClasses.requests' => function ($query) {
                $query->latest();
            }
        ])
            // ->where('student_id', $student->id)
            ->get()
            ->map(function ($courseTa) {
                $latestRequest = $courseTa->courseTaClasses->flatMap->requests->sortByDesc('created_at')->first();

                return [
                    'student_id' => $courseTa->student->student_id,
                    'full_name' => $courseTa->student->name,
                    'course' => $courseTa->course->subjects->subject_id . ' ' . $courseTa->course->subjects->name_en,
                    'applied_at' => $courseTa->created_at,
                    'status' => $latestRequest ? $latestRequest->status : null,
                    'approved_at' => $latestRequest ? $latestRequest->approved_at : null,
                    'comment' => $latestRequest ? $latestRequest->comment : null,
                ];
            });        return view('adminHome', compact('requests'));
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
