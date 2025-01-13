<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Students;
use Illuminate\Http\Request;
use App\Models\CourseTas;
use App\Models\Requests;
use App\Models\Teaching;
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

        $teaching_assistants = CourseTas::with(['student', 'courseTaClasses.requests'])
            ->where('course_id', $course_id)
            ->get()
            ->map(function ($ta) {
                // Get the latest request status for this TA
                $latestRequest = $ta->courseTaClasses
                    ->flatMap->requests
                    ->sortByDesc('created_at')
                    ->first();

                return [
                    'id' => $ta->id,
                    'name' => $ta->student->name,
                    'email' => $ta->student->email,
                    'student_id' => $ta->student->student_id,
                    'status' => $latestRequest ? strtolower($latestRequest->status) : 'w'
                ];
            });

        $course['teaching_assistants'] = $teaching_assistants;

        return view('layouts.teacher.subjectDetail', compact('course'));
    } catch (\Exception $e) {
        Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล');
        }
    }

    public function taDetail($ta_id) {
        try {
            $ta = CourseTas::with(['student', 'course.semesters'])->findOrFail($ta_id);
            $student = $ta->student;
            $semester = $ta->course->semesters;
            
            $start = \Carbon\Carbon::parse($semester->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($semester->end_date)->endOfDay();
            
            if (!\Carbon\Carbon::now()->between($start, $end)) {
                return back()->with('error', 'ไม่อยู่ในช่วงภาคการศึกษาปัจจุบัน');
            }
    
            // สร้างรายการเดือน
            $monthsInSemester = [];
            
            // ถ้าปีเดียวกัน
            if ($start->year === $end->year) {
                for ($m = $start->month; $m <= $end->month; $m++) {
                    $date = \Carbon\Carbon::createFromDate($start->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
            } 
            // ถ้าคนละปี
            else {
                // เพิ่มเดือนของปีแรก
                for ($m = $start->month; $m <= 12; $m++) {
                    $date = \Carbon\Carbon::createFromDate($start->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
                
                // เพิ่มเดือนของปีถัดไป
                for ($m = 1; $m <= $end->month; $m++) {
                    $date = \Carbon\Carbon::createFromDate($end->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
            }
    
            // Get selected month from request, default to start month/year
            $selectedYearMonth = request('month', $start->format('Y-m'));
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);
    
            // Query with month filter
            $teachings = Teaching::with(['attendance', 'teacher', 'class'])
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereBetween('start_time', [$start, $end])
                ->whereHas('attendance', function($query) {
                    $query->whereIn('status', ['เข้าปฏิบัติการสอน', 'ลา']);
                })
                ->whereYear('start_time', $selectedDate->year)
                ->whereMonth('start_time', $selectedDate->month)
                ->get();
            
            return view('layouts.teacher.taDetail', compact(
                'student',
                'semester',
                'teachings',
                'monthsInSemester',
                'selectedYearMonth'
            ));
            
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
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
