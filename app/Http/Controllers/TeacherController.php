<?php

namespace App\Http\Controllers;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Students;
use App\Models\Requests;
use Illuminate\Support\Facades\Auth;

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

    public function subjectTeacher()
    {
        $user = Auth::user();
        $teacher = Teachers::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลอาจารย์');
        }

        $subjects = Subjects::whereHas('courses', function ($query) use ($teacher) {
            $query->where('owner_teacher_id', $teacher->id);
        })->with([
                    'courses' => function ($query) use ($teacher) {
                        $query->where('owner_teacher_id', $teacher->id);
                    }
                ])->get();

        return view('layouts.teacher.subject', compact('subjects'));
    }

    public function showTARequests()
    {
        $user = Auth::user();
        $teacher = Teachers::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลอาจารย์');
        }

        $requests = Requests::with([
            'courseTas',
            'courseTas.course',
            'courseTas.course.subjects',
            'courseTas.student'
        ])->whereHas('courseTas.course', function ($query) use ($teacher) {
            $query->where('owner_teacher_id', $teacher->id);
        })->get();
        // dd($requests->toArray());

        return view('teacherHome', compact('requests'));
    }

    public function updateTARequestStatus(\Illuminate\Http\Request $request)
    {
        $taRequest = Requests::findOrFail($request->request_id);
        $taRequest->status = $request->status;
        $taRequest->comment = $request->comment;
        $taRequest->approved_at = now();
        $taRequest->save();

        return redirect()->back()->with('success', 'อัพเดทสถานะสำเร็จ');
    }
}
