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
        $course = Courses::with([
            'subjects',
            'teachers',
            'semesters',
            'course_tas.student',
            'course_tas.courseTaClasses.requests' => function ($query) {
                $query->where('status', 'A')
                    ->whereNotNull('approved_at');
            }
        ])
            ->where('course_id', $course_id)
            ->firstOrFail();

        return view('layouts.admin.detailsTa', compact('course'));
    }
    
    public function taDetail()
    {
        return view('layouts.teacher.taDetail');
    }

    public function subjectTeacher()
    {
        try {
            $user = Auth::user();

            $localTeacher = Teachers::where('user_id', $user->id)->first();

            $tdbmService = new TDBMApiService();
            $teachers = collect($tdbmService->getTeachers());

            // Get current date
            $currentDate = now();
            $month = $currentDate->month;
            $year = $currentDate->year + 543; // Convert to Buddhist year

            // Determine semester based on month
            if ($month >= 6 && $month <= 11) {
                $semester = 1;
            } else {
                $semester = 2;
                // Adjust year for second semester spanning across years
                if ($month >= 1 && $month <= 5) {
                    $year -= 1;
                }
            }

            $semesterId = $year . $semester;

            Log::info('Academic Calendar Info:', [
                'current_date' => $currentDate,
                'thai_year' => $year,
                'semester' => $semester,
                'semester_id' => $semesterId
            ]);

            $teacher = null;

            $teacher = $teachers->where('account_user_id', $user->id)->first();

            if (!$teacher && $localTeacher && $localTeacher->email) {
                $teacher = $teachers->where('email', $localTeacher->email)->first();
            }

            if (!$teacher && $localTeacher && $localTeacher->name) {
                $teacher = $teachers->where('name', 'like', '%' . $localTeacher->name . '%')->first();
            }

            if (!$teacher && $localTeacher && $localTeacher->id) {
                $teacher = $teachers->where('teacher_id', $localTeacher->id)->first();
            }

            if (!$teacher) {
                if ($localTeacher) {
                    $teacher = [
                        'teacher_id' => $localTeacher->id,
                        'name' => $localTeacher->name,
                        'email' => $localTeacher->email,
                        'prefix' => $localTeacher->prefix ?? '',
                        'position' => $localTeacher->position ?? '',
                        'degree' => $localTeacher->degree ?? ''
                    ];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่พบข้อมูลอาจารย์',
                        'debug_info' => [
                            'user_id' => $user->id,
                            'local_teacher_exists' => $localTeacher ? 'yes' : 'no'
                        ]
                    ], 404);
                }
            }

            $allSubjects = collect($tdbmService->getSubjects());
            $allStudentClasses = collect($tdbmService->getStudentClasses());
            $allCourses = collect($tdbmService->getCourses());

            // Filter student classes by current semester and teacher
            $teacherClasses = $allStudentClasses->where('teacher_id', $teacher['teacher_id'])
                ->where('status', 'A')
                ->where('semester_id', $semesterId);

            // Get course IDs from student classes
            $courseIds = $teacherClasses->pluck('course_id')->unique();

            // Get corresponding courses
            $teacherCourses = $allCourses->whereIn('course_id', $courseIds)
                ->where('status', 'A')
                ->where('semester_id', $semesterId);

            $teacherSubjectIds = $teacherCourses->pluck('subject_id')->unique();

            // Initialize empty array for subjects
            $subjects = [];

            // Only process subjects if we have any matching subject IDs
            if ($teacherSubjectIds->isNotEmpty()) {
                $subjects = $allSubjects->whereIn('subject_id', $teacherSubjectIds)
                    ->map(function ($subjectItem) use ($teacherCourses, $teacherClasses) {
                        $coursesForSubject = $teacherCourses->where('subject_id', $subjectItem['subject_id']);

                        // Add class information to each course
                        $coursesWithClasses = $coursesForSubject->map(function ($course) use ($teacherClasses) {
                            $classes = $teacherClasses->where('course_id', $course['course_id'])
                                ->values()
                                ->toArray();
                            $course['classes'] = $classes;
                            return $course;
                        });

                        return [
                            'subject_id' => $subjectItem['subject_id'] ?? '',
                            'name_en' => $subjectItem['name_en'] ?? '',
                            'courses' => $coursesWithClasses->values()->toArray()
                        ];
                    })
                    ->values()
                    ->toArray();
            }

            // Add teacher info to response
            $teacherInfo = [
                'id' => $teacher['teacher_id'],
                'name' => ($teacher['prefix'] ?? '') .
                    ($teacher['position'] ?? '') .
                    ($teacher['degree'] ?? '') .
                    $teacher['name'],
                'email' => $teacher['email'],
                'current_semester' => [
                    'id' => $semesterId,
                    'year' => $year,
                    'semester' => $semester
                ]
            ];

            return view('layouts.teacher.subject', [
                'subjects' => $subjects,
                'teacher' => $teacherInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error in subjectTeacher: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล กรุณาลองใหม่อีกครั้ง',
                'error' => $e->getMessage()
            ], 500);
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
