<?php

namespace App\Http\Controllers;

use App\Models\Attendances;
use App\Models\Classes;
use App\Models\Courses;
use App\Models\CourseTaClasses;
use App\Models\Teaching;
use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\Students;
use App\Models\Announce;
use App\Models\Requests;
use App\Models\CourseTas;
use App\Models\Curriculums;
use App\Models\Semesters;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Support\Str;
use App\Services\TDBMApiService;
use Yoeunes\Toastr\Facades\Toastr;


class TaController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    private $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->middleware('auth');
        $this->tdbmService = $tdbmService;
    }



    /// TA ROLE

    // public function request()
    // {
    //     $subjects = Subjects::all();
    //     $subjectsWithSections = [];

    //     foreach ($subjects as $subject) {
    //         $course = Courses::where('subject_id', $subject->subject_id)->first();
    //         if ($course) {
    //             $sections = Classes::where('course_id', $course->id)->pluck('section_num')->toArray();
    //             $subjectsWithSections[] = [
    //                 'subject' => $subject,
    //                 'sections' => $sections
    //             ];
    //         }
    //     }

    //     return view('layouts.ta.request', compact('subjectsWithSections'));
    // }


    public function request()
    {
        // Get current semester
        $currentSemester = collect($this->tdbmService->getSemesters())
            // ตรวจสอบว่าวันที่ปัจจุบันอยู่ระหว่าง start_date และ end_date
            ->filter(function ($semester) {
                $startDate = \Carbon\Carbon::parse($semester['start_date']);
                $endDate = \Carbon\Carbon::parse($semester['end_date']);
                $now = \Carbon\Carbon::now();
                return $now->between($startDate, $endDate);
            })->first();

        if (!$currentSemester) { //ตรวจสอบว่ามีเทอมปัจจุบันหรือไม่
            return redirect()->back()->with('error', 'ขณะนี้ไม่อยู่ในช่วงเวลารับสมัคร TA');
        }

        //ดึงข้อมูลที่เกี่ยวข้องกับเทอมปัจจุบัน
        $subjects = collect($this->tdbmService->getSubjects());
        $courses = collect($this->tdbmService->getCourses())
            ->where('semester_id', $currentSemester['semester_id']);
        $studentClasses = collect($this->tdbmService->getStudentClasses())
            ->where('semester_id', $currentSemester['semester_id']);

        // กรองเฉพาะวิชาที่มีการเปิดสอนในเทอมปัจจุบัน
        $subjectsWithSections = $subjects
            ->filter(function ($subject) use ($courses) {
                return $courses->where('subject_id', $subject['subject_id'])->isNotEmpty();
            })
            ->map(function ($subject) use ($courses, $studentClasses) {
                $course = $courses->where('subject_id', $subject['subject_id'])->first();
                $sections = $studentClasses->where('course_id', $course['course_id'])
                    ->pluck('section_num') //ดึงเฉพาะข้อมูลในคอลัมน์ section_num
                    ->unique() //กรองข้อมูลที่ซ้ำกันออก เหลือแค่ค่าที่ไม่ซ้ำ
                    ->values()
                    ->toArray();

                return [
                    'subject' => [
                        'subject_id' => $subject['subject_id'],
                        'subject_name_en' => $subject['name_en'],
                        'subject_name_th' => $subject['name_th']
                    ],
                    'sections' => $sections
                ];
            })->values();

        return view('layouts.ta.request', compact('subjectsWithSections', 'currentSemester'));
    }



    public function taSubject()
    {
        return view('layouts.ta.taSubject');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function attendances()
    {
        return view('layouts.ta.attendances');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function showAnnounces()
    {
        $announces = Announce::orderBy('created_at', 'desc')->get();
        return view('home', compact('announces'));
    }
    public function showRequestForm()
    {
        $user = Auth::user();
        return view('layouts.ta.request', compact('user'));
    }

    public function apply(Request $request)
    {
        try {
            DB::beginTransaction();

            // 1. Validate request
            $request->validate([
                'applications' => 'required|array|min:1|max:3',
                'applications.*.subject_id' => 'required',
                'applications.*.sections' => 'required|array|min:1',
                'applications.*.sections.*' => 'required|numeric',
            ]);

            $user = Auth::user();

            // 2. Get API data
            $courses = collect($this->tdbmService->getCourses());
            $studentClasses = collect($this->tdbmService->getStudentClasses());
            $subjects = collect($this->tdbmService->getSubjects());

            // 3. Get current semester
            $currentSemester = collect($this->tdbmService->getSemesters())
                ->filter(function ($semester) {
                    $startDate = \Carbon\Carbon::parse($semester['start_date']);
                    $endDate = \Carbon\Carbon::parse($semester['end_date']);
                    return now()->between($startDate, $endDate);
                })->first();

            if (!$currentSemester) {
                Toastr()->error('ไม่อยู่ในช่วงเวลารับสมัคร', 'เกิดข้อผิดพลาด!');
                return redirect()->back();
            }

            // 4. Create or update semester
            $localSemester = Semesters::firstOrCreate(
                [
                    'semester_id' => $currentSemester['semester_id']
                ],
                [
                    'year' => intval($currentSemester['year']),
                    'semesters' => intval($currentSemester['semester']), // แก้จาก semester เป็น semesters
                    'start_date' => $currentSemester['start_date'],
                    'end_date' => $currentSemester['end_date']
                ]
            );

            // 5. Create or update student
            $student = Students::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'prefix' => $user->prefix,
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'email' => $user->email,
                    'card_id' => $user->card_id,
                    'phone' => $user->phone,
                ]
            );

            $applications = $request->input('applications');

            // 6. Check course limit
            $currentCourseCount = CourseTas::where('student_id', $student->id)->count();
            if ($currentCourseCount + count($applications) > 3) {
                Toastr()->warning('คุณไม่สามารถสมัครเป็นผู้ช่วยสอนได้เกิน 3 วิชา', 'คำเตือน!');
                return redirect()->back();
            }

            // 7. Process each application
            foreach ($applications as $application) {
                $subjectId = $application['subject_id'];
                $sectionNums = $application['sections'];

                // Find course from API
                $course = $courses->where('subject_id', $subjectId)
                    ->where('semester_id', $currentSemester['semester_id'])
                    ->first();

                if (!$course) {
                    DB::rollBack();
                    Toastr()->error('ไม่พบรายวิชา ' . $subjectId . ' ในระบบ', 'เกิดข้อผิดพลาด!');
                    return redirect()->back();
                }

                // Get subject data
                $subjectData = $subjects->where('subject_id', $course['subject_id'])->first();
                if (!$subjectData) {
                    DB::rollBack();
                    Toastr()->error('ไม่พบข้อมูลรายวิชา ' . $subjectId, 'เกิดข้อผิดพลาด!');
                    return redirect()->back();
                }

                // Check duplicate
                $existingTA = CourseTas::where('student_id', $student->id)
                    ->where('course_id', $course['course_id'])
                    ->first();

                if ($existingTA) {
                    DB::rollBack();
                    Toastr()->warning('คุณได้สมัครเป็นผู้ช่วยสอนในวิชา ' . $subjectId . ' แล้ว', 'คำเตือน!');
                    return redirect()->back();
                }

                // Create subject
                $localSubject = Subjects::firstOrCreate(
                    ['subject_id' => $subjectData['subject_id']],
                    [
                        'name_th' => $subjectData['name_th'],
                        'name_en' => $subjectData['name_en'],
                        'credit' => $subjectData['credit'],
                        'cur_id' => $subjectData['cur_id'],
                        'weight' => $subjectData['weight'] ?? null,
                        'detail' => $subjectData['detail'] ?? null,
                        'status' => 'A'
                    ]
                );

                // Debug ข้อมูลที่ได้จาก API ก่อนสร้าง course
                Log::info('Course data from API:', [
                    'course' => $course,
                    'owner_teacher_id' => $course['owner_teacher_id'] ?? null
                ]);

                // ตรวจสอบว่ามี owner_teacher_id ก่อนสร้าง course
                if (!isset($course['owner_teacher_id']) || empty($course['owner_teacher_id'])) {
                    Log::error('Missing owner_teacher_id:', [
                        'course_id' => $course['course_id'],
                        'subject_id' => $subjectId
                    ]);
                    DB::rollBack();
                    Toastr()->error('ไม่พบข้อมูลอาจารย์ผู้สอนสำหรับรายวิชา ' . $subjectId, 'เกิดข้อผิดพลาด!');
                    return redirect()->back();
                }

                // สร้าง course โดยตรวจสอบข้อมูลที่จำเป็นทั้งหมด
                $localCourse = Courses::firstOrCreate(
                    ['course_id' => $course['course_id']],
                    [
                        'subject_id' => $localSubject->subject_id,
                        'semester_id' => $localSemester->semester_id,
                        'owner_teacher_id' => $course['owner_teacher_id'], // ต้องไม่เป็น null
                        'major_id' => $course['major_id'] ?: null,  // ถ้าไม่มีให้เป็น null
                        'cur_id' => $course['cur_id'] ?: '1',      // ถ้าไม่มีให้เป็น '1'
                        'status' => $course['status'] ?: 'A'       // ถ้าไม่มีให้เป็น 'A'
                    ]
                );

                // Create course TA
                $courseTA = CourseTas::create([
                    'student_id' => $student->id,
                    'course_id' => $localCourse->course_id,
                ]);

                // Process sections
                foreach ($sectionNums as $sectionNum) {
                    $class = $studentClasses->where('course_id', $course['course_id'])
                        ->where('section_num', $sectionNum)
                        ->first();

                    if (!$class) {
                        DB::rollBack();
                        Toastr()->error('ไม่พบเซคชัน ' . $sectionNum . ' สำหรับวิชา ' . $subjectId, 'เกิดข้อผิดพลาด!');
                        return redirect()->back();
                    }

                    $localClass = Classes::firstOrCreate(
                        ['class_id' => $class['class_id']],
                        [
                            'section_num' => $class['section_num'],
                            'course_id' => $localCourse->course_id,
                            'title' => $class['title'] ?? null,
                            'status' => $class['status'],
                            'open_num' => $class['open_num'] ?? 0,        // เพิ่มฟิลด์ที่จำเป็น
                            'enrolled_num' => $class['enrolled_num'] ?? 0, // กำหนดค่าเริ่มต้นเป็น 0
                            'available_num' => $class['available_num'] ?? 0,
                            'teacher_id' => $class['teacher_id'],         // ต้องระบุค่า foreign key
                            'semester_id' => $class['semester_id'],       // ต้องระบุค่า foreign key
                            'major_id' => $class['major_id']             // ต้องระบุค่า foreign key
                        ]
                    );

                    $courseTaClass = CourseTaClasses::create([
                        'class_id' => $localClass->class_id,
                        'course_ta_id' => $courseTA->id,
                    ]);

                    Requests::create([
                        'course_ta_class_id' => $courseTaClass->id,
                        'status' => 'W',
                        'comment' => null,
                        'approved_at' => null,
                    ]);
                }
            }

            DB::commit();
            Toastr()->success('สมัครเป็นผู้ช่วยสอนสำเร็จ', 'สำเร็จ!');
            return redirect()->route('layout.ta.request');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr()->error('เกิดข้อผิดพลาด: ' . $e->getMessage(), 'เกิดข้อผิดพลาด!');
            return redirect()->back();
        }
    }

    public function getSections($course_id)
    {
        // ดึง sections ทั้งหมดของ course_id นั้น
        $sections = Classes::where('course_id', $course_id)->get(['id', 'section_num']);

        // ส่งข้อมูลกลับในรูปแบบ JSON
        return response()->json($sections);
    }

    public function showCourseTas()
{
    $user = Auth::user();
    $student = Students::where('user_id', $user->id)->first();

    if (!$student) {
        return redirect()->route('layout.ta.request')->with('error', 'ไม่พบข้อมูลนักศึกษา');
    }

    // เพิ่ม requests เข้าไปใน with และเพิ่มเงื่อนไขการกรอง
    $courseTaClasses = CourseTaClasses::with([
        'courseTa.course.subjects',
        'courseTa.course.semesters',
        'courseTa.course.teachers',
        'courseTa.course.curriculums',
        'class',
        'requests'  // เพิ่มการดึงข้อมูล requests
    ])
    ->whereHas('courseTa', function ($query) use ($student) {
        $query->where('student_id', $student->id);
    })
    ->whereHas('requests', function($query) {
        $query->where('status', 'A')  // กรองเฉพาะ status = 'A' (Approve)
              ->whereNotNull('approved_at');  // และต้องมี approved_at
    })
    ->get();

    // ประมวลผลข้อมูลหลักสูตร
    foreach ($courseTaClasses as $courseTaClass) {
        if (isset($courseTaClass->courseTa->course->curriculums->name_th)) {
            $courseTaClass->courseTa->course->curriculums->name_th = 
                trim(Str::before($courseTaClass->courseTa->course->curriculums->name_th, ' '));
        }
    }

    return view('layouts.ta.subjectList', compact('courseTaClasses'));
}

    public function showSubjectDetail($id, $classId)
    {
        $user = Auth::user();
        $student = Students::where('user_id', $user->id)->first();

        // ดึงข้อมูลเฉพาะที่มี course_ta id ตรงกับ $id ที่ส่งมา และกรองด้วย student_id
        $courseTaClass = CourseTaClasses::with([
            'class.course.subjects',    // ดึงข้อมูลวิชา
            'class.course.curriculums', // ดึงข้อมูลหลักสูตร
            'class.teachers',           // ดึงข้อมูลอาจารย์ที่สอน
            'class.semesters'           // ดึงข้อมูลปีการศึกษาและเทอม
        ])->whereHas('courseTa', function ($query) use ($student) {
            // กรองด้วย student_id จากตาราง course_tas
            $query->where('student_id', $student->id);
        })->where('course_ta_id', $id)  // กรองด้วย course_ta_id ที่ส่งมา
            ->where('class_id', $classId)  // กรองด้วย class_id
            ->first(); // ใช้ first() เพื่อดึงข้อมูลเฉพาะ 1 รายการ 

        // ตรวจสอบว่าข้อมูลถูกต้องหรือไม่
        if (!$courseTaClass) {
            abort(404, 'ไม่พบข้อมูล CourseTa ที่ระบุ');
        }

        return view('layouts.ta.subjectDetail', compact('courseTaClass', 'student'));
    }

    public function showTeachingData($id)
    {
        // ดึงข้อมูลของ class จาก id ที่ส่งมา พร้อมกับข้อมูลการสอน (teaching) และอาจารย์ (teachers)
        $teachings = Teaching::with([
            'class',          // เชื่อมต่อกับตาราง classes
            'teacher'         // เชื่อมต่อกับตาราง teachers
        ])->where('class_id', $id)  // กรองด้วย class_id ที่ส่งมา
            ->get();

        // ส่งข้อมูลไปที่ view
        return view('layouts.ta.teaching', compact('teachings'));
    }

    public function showAttendanceForm($teaching_id)
    {
        // Find the teaching session by ID
        $teaching = Teaching::findOrFail($teaching_id);
        return view('layouts.ta.attendances', compact('teaching'));
    }

    // public function submitAttendance(Request $request, $teaching_id)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'status' => 'required', // Either 'เข้าปฏิบัติการสอน' or 'ลา'
    //         'note' => 'nullable|string',
    //     ]);

    //     $user = Auth::user();
    //     $student = Students::where('user_id', $user->id)->first();

    //     // Insert into the attendances table
    //     Attendances::create([
    //         'status' => $request->status,
    //         'approve_at' => null,  // Set as null initially
    //         'approve_user_id' => null,  // Set as null initially
    //         'note' => $request->note,
    //         'user_id' => $user->id,
    //         'teaching_id' => $teaching_id,
    //         'student_id' => $student->id,
    //     ]);

    //     // Update the teaching status in the teaching table
    //     $teaching = Teaching::findOrFail($teaching_id);
    //     $teaching->status = $request->status === 'เข้าปฏิบัติการสอน' ? 'S' : 'L'; // 'S' for success, 'L' for leave
    //     $teaching->save();

    //     // Redirect back to the form or some confirmation page
    //     return redirect()->route('layout.ta.teaching', ['id' => $teaching->class_id])
    //         ->with('success', 'บันทึกข้อมูลสำเร็จ');
    // }

    public function submitAttendance(Request $request, $teaching_id)
    {
        // Validate the request
        $request->validate([
            'status' => 'required', // Either 'เข้าปฏิบัติการสอน' or 'ลา'
            'note' => 'required|string',  // 'note' is now required
        ]);

        $user = Auth::user();
        $student = Students::where('user_id', $user->id)->first();

        // Insert into the attendances table
        Attendances::create([
            'status' => $request->status,
            'approve_at' => null,  // Set as null initially
            'approve_user_id' => null,  // Set as null initially
            'note' => $request->note,
            'user_id' => $user->id,
            'teaching_id' => $teaching_id,
            'student_id' => $student->id,
        ]);

        // Update the teaching status in the teaching table
        $teaching = Teaching::findOrFail($teaching_id);
        $teaching->status = $request->status === 'เข้าปฏิบัติการสอน' ? 'S' : 'L'; // 'S' for success, 'L' for leave
        $teaching->save();

        // Redirect back to the form or some confirmation page
        return redirect()->route('layout.ta.teaching', ['id' => $teaching->class_id])
            ->with('success', 'บันทึกข้อมูลสำเร็จ');
    }
}
