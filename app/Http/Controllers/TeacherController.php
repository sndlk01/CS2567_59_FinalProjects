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
use App\Services\TDBMApiService;



class TeacherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TDBMApiService $tdbmService)
    {
        $this->middleware('auth');
        $this->tdbmService = $tdbmService;
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

    public function showTARequests(TDBMApiService $tdbmApiService)
    {
        $user = Auth::user();
        $teacher = Teachers::where('user_id', $user->id)->first();

        if (!$teacher) {
            Log::error('ไม่พบข้อมูลอาจารย์สำหรับ user_id: ' . $user->id);
            return redirect()->back()->with('error', 'ไม่พบข้อมูลอาจารย์');
        }

        try {
            $apiCourses = collect($tdbmApiService->getCourses());
            $apiSubjects = collect($tdbmApiService->getSubjects());

            $teacherCourseIds = $apiCourses
                ->where('owner_teacher_id', (string) $teacher->teacher_id)  // เปลี่ยนจาก id เป็น teacher_id
                ->where('status', 'A')
                ->pluck('course_id')
                ->toArray();

            $courseTas = CourseTas::with(['student', 'courseTaClasses.requests'])
                ->whereIn('course_id', $teacherCourseIds)
                ->get();

            // แปลงข้อมูล
            $formattedCourseTas = $courseTas->map(function ($courseTa) use ($apiCourses, $apiSubjects) {
                $course = $apiCourses->firstWhere('course_id', $courseTa->course_id);
                if (!$course) {
                    Log::warning('Course not found for ID: ' . $courseTa->course_id);
                    return null;
                }

                $subject = $apiSubjects->firstWhere('subject_id', $course['subject_id']);
                if (!$subject) {
                    Log::warning('Subject not found for ID: ' . $course['subject_id']);
                    return null;
                }

                $latestRequest = $courseTa->courseTaClasses->flatMap->requests->sortByDesc('created_at')->first();

                return [
                    'course_ta_id' => $courseTa->id,
                    'course_id' => $courseTa->course_id,
                    'course' => $subject['subject_id'] . ' ' . $subject['name_en'],
                    'student_id' => $courseTa->student->student_id,
                    'student_name' => $courseTa->student->name ,
                    'status' => $latestRequest ? strtolower($latestRequest->status) : 'w',
                    'approved_at' => $latestRequest ? $latestRequest->approved_at : null,
                    'comment' => $latestRequest ? $latestRequest->comment : '',
                ];
            })->filter(); // กรองค่า null ออก

            Log::info('Formatted Course TAs count: ' . $formattedCourseTas->count());

            return view('teacherHome', ['courseTas' => $formattedCourseTas]);

        } catch (\Exception $e) {
            Log::error('Error in showTARequests: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
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
