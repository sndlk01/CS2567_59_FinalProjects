<?php

namespace App\Http\Controllers;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Students;
use Illuminate\Http\Request;
use App\Models\CourseTas;
use App\Models\Requests;
use App\Models\CourseTaClasses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
            Log::error('ไม่พบข้อมูลอาจารย์สำหรับ user_id: ' . $user->id);
            return redirect()->back()->with('error', 'ไม่พบข้อมูลอาจารย์');
        }

        $courseTas = CourseTas::with(['student', 'course.subjects', 'courseTaClasses.requests'])
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('owner_teacher_id', $teacher->id);
            })
            ->get()
            ->map(function ($courseTa) {
                $latestRequest = $courseTa->courseTaClasses->flatMap->requests->sortByDesc('created_at')->first();
                return [
                    'course_ta_id' => $courseTa->id,
                    'course_id' => $courseTa->course_id,
                    'course' => $courseTa->course->subjects->subject_id . ' ' . $courseTa->course->subjects->name_en,
                    'student_id' => $courseTa->student->student_id,
                    'student_name' => $courseTa->student->fname . ' ' . $courseTa->student->lname,
                    'status' => $latestRequest ? $latestRequest->status : null,
                    'approved_at' => $latestRequest ? $latestRequest->approved_at : null,
                    'comment' => $latestRequest ? $latestRequest->comment : null,
                ];
            });

        Log::info('จำนวนคำขอ TA: ' . $courseTas->count());

        return view('teacherHome', compact('courseTas'));
    }

    public function updateTARequestStatus(Request $request)
    {
        $courseTaIds = $request->input('course_ta_ids', []);
        $statuses = $request->input('statuses', []);
        $comments = $request->input('comments', []);

        foreach ($courseTaIds as $index => $courseTaId) {
            $courseTaClass = CourseTaClasses::where('course_ta_id', $courseTaId)->first();
            
            if ($courseTaClass) {
                Requests::updateOrCreate(
                    ['course_ta_class_id' => $courseTaClass->id],
                    [
                        'status' => $statuses[$index],
                        'comment' => $comments[$index],
                        'approved_at' => now(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'อัพเดทสถานะสำเร็จ');
    }
}
