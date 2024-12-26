<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Students;
use Illuminate\Http\Request;
use App\Models\CourseTas;
use App\Models\Requests;
use App\Models\Attendances;
use App\Models\CourseTaClasses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TDBMApiService;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    private $tdbmService;
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

    public function subjectDetail($course_id)
    {
        try {
            $currentDate = now();
            $semester = ($currentDate->month >= 6 && $currentDate->month <= 11) ? 1 : 2;
            $year = $currentDate->year + 543;
            if ($currentDate->month >= 1 && $currentDate->month <= 5) {
                $year -= 1;
            }

            $tdbmService = new TDBMApiService();
            $course = collect($tdbmService->getCourses())->firstWhere('course_id', $course_id);

            if (!$course) {
                throw new \Exception('Course not found');
            }

            $subject = collect($tdbmService->getSubjects())->firstWhere('subject_id', $course['subject_id']);
            $teacher = collect($tdbmService->getTeachers())->firstWhere('teacher_id', $course['owner_teacher_id']);

            $course['subject'] = $subject;
            $course['teacher'] = $teacher;
            $course['current_semester'] = [
                'semester' => $semester,
                'year' => $year
            ];

            $teaching_assistants = CourseTas::with('student')
                ->where('course_id', $course_id)
                ->get()
                ->map(function ($ta) {
                    return [
                        'id' => $ta->id,
                        'name' => $ta->student->name,
                        'email' => $ta->student->email,
                        'student_id' => $ta->student->student_id,
                    ];
                });

            $course['teaching_assistants'] = $teaching_assistants;

            return view('layouts.teacher.subjectDetail', compact('course'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล');
        }
    }

    public function taDetail($ta_id)
    {
        try {
            $ta = CourseTas::with(['student', 'course.semesters'])->findOrFail($ta_id);
            $student = $ta->student;
            $semester = $ta->course->semesters;

            $currentDate = \Carbon\Carbon::now();
            $start = \Carbon\Carbon::parse($semester->start_date);
            $end = \Carbon\Carbon::parse($semester->end_date);

            if (!$currentDate->between($start, $end)) {
                return back()->with('error', 'ไม่อยู่ในช่วงภาคการศึกษาปัจจุบัน');
            }

            $selectedMonth = request('month', $start->month);

            $attendances = Attendances::where('student_id', $student->id)
                ->whereYear('created_at', $start->year)
                ->whereMonth('created_at', $selectedMonth)
                ->get();

            $monthsInSemester = [];
            $startMonth = $start->month;
            $endMonth = $end->month;

            if ($start->year == $end->year) {
                for ($m = $startMonth; $m <= $endMonth; $m++) {
                    $date = \Carbon\Carbon::create($start->year, $m, 1);
                    $monthsInSemester[$m] = $date->locale('th')->monthName;
                }
            } else {
                for ($m = $startMonth; $m <= 12; $m++) {
                    $date = \Carbon\Carbon::create($start->year, $m, 1);
                    $monthsInSemester[$m] = $date->locale('th')->monthName;
                }
                for ($m = 1; $m <= $endMonth; $m++) {
                    $date = \Carbon\Carbon::create($end->year, $m, 1);
                    $monthsInSemester[$m] = $date->locale('th')->monthName;
                }
            }

            return view('layouts.teacher.taDetail', compact(
                'student',
                'semester',
                'attendances',
                'monthsInSemester'
            ));

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล');
        }
    }



    public function approveAttendance(Request $request)
    {
        try {
            $status = $request->batch_status;
            $attendanceIds = $request->input('attendance_ids', []);

            Attendances::whereIn('id', $attendanceIds)
                ->update([
                    'status' => $status,
                    'approve_at' => now(),
                    'approve_user_id' => auth()->id()
                ]);

            return back()->with('success', 'บันทึกการอนุมัติเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
    }

    public function subjectTeacher()
    {
        try {
            $user = Auth::user();
            $localTeacher = Teachers::where('user_id', $user->id)->first();
            $tdbmService = new TDBMApiService();
            $allCourses = collect($tdbmService->getCourses());
            $allSubjects = collect($tdbmService->getSubjects());

            // Get current semester courses with TAs
            $teacherCourses = $allCourses->where('owner_teacher_id', $localTeacher->teacher_id);

            $subjectsWithTAs = CourseTas::whereIn('course_id', $teacherCourses->pluck('course_id'))
                ->select('course_id', DB::raw('COUNT(DISTINCT student_id) as ta_count'))
                ->groupBy('course_id')
                ->get();

            $subjects = [];
            foreach ($subjectsWithTAs as $courseTa) {
                $course = $teacherCourses->firstWhere('course_id', $courseTa->course_id);
                if (!$course)
                    continue;

                $subject = $allSubjects->firstWhere('subject_id', $course['subject_id']);
                if (!$subject)
                    continue;

                $subjectId = $subject['subject_id'];
                if (!isset($subjects[$subjectId])) {
                    $subjects[$subjectId] = [
                        'subject_id' => $subjectId,
                        'name_en' => $subject['name_en'],
                        'ta_count' => $courseTa->ta_count,
                        'courses' => [$course]
                    ];
                } else {
                    $subjects[$subjectId]['ta_count'] += $courseTa->ta_count;
                    $subjects[$subjectId]['courses'][] = $course;
                }
            }

            return view('layouts.teacher.subject', ['subjects' => array_values($subjects)]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'], 500);
        }
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
                    'student_name' => $courseTa->student->name,
                    // 'student_name' => $courseTa->student->name,
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
            // ดึงทุก course_ta_classes ที่เกี่ยวข้องกับ course_ta_id นี้
            $courseTaClasses = CourseTaClasses::where('course_ta_id', $courseTaId)->get();

            if ($courseTaClasses->isNotEmpty()) {
                foreach ($courseTaClasses as $courseTaClass) {
                    // สร้างหรืออัพเดท request สำหรับแต่ละ class
                    Requests::create([
                        'course_ta_class_id' => $courseTaClass->id,
                        'status' => $statuses[$index],
                        'comment' => $comments[$index],
                        'approved_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'อัพเดทสถานะสำเร็จ');
    }
}
