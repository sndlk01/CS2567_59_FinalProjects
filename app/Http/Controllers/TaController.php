<?php

namespace App\Http\Controllers;

use App\Models\Attendances;
use App\Models\Classes;
use App\Models\Courses;
use App\Models\CourseTaClasses;
use App\Models\ExtraAttendances;
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
    private $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->middleware('auth');
        $this->tdbmService = $tdbmService;
    }

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

    public function showCourseTas()
    {
        $user = Auth::user();
        $student = Students::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('layout.ta.request')->with('error', 'ไม่พบข้อมูลนักศึกษา');
        }

        $courseTaClasses = CourseTaClasses::with([
            'courseTa.course.subjects',
            'courseTa.course.semesters',
            'courseTa.course.teachers',
            'courseTa.course.curriculums',
            'class',
            'requests'
        ])
            ->whereHas('courseTa', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('requests')
                    ->whereColumn('requests.course_ta_class_id', 'course_ta_classes.id')
                    ->where('status', 'A')
                    ->whereNotNull('approved_at');
            })
            ->get();

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
        try {


            $user = Auth::user();
            $student = Students::where('user_id', $user->id)->first();

            // ดึงข้อมูล CourseTaClass
            $courseTaClass = CourseTaClasses::with([
                'class.course.subjects',
                'class.course.curriculums',
                'class.teachers',
                'class.semesters'
            ])->whereHas('courseTa', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })->where('course_ta_id', $id)
                ->where('class_id', $classId)
                ->first();

            // ตรวจสอบว่าข้อมูลถูกต้องหรือไม่
            if (!$courseTaClass) {
                abort(404, 'ไม่พบข้อมูล CourseTa ที่ระบุ');
            }

            // ดึงข้อมูลเทอมจาก relation
            $semester = $courseTaClass->class->semesters;

            // สร้าง array เดือนในเทอม
            $months = [];
            if ($semester) {
                $startDate = \Carbon\Carbon::parse($semester->start_date);
                $endDate = \Carbon\Carbon::parse($semester->end_date);

                // เพิ่ม endOfMonth() เพื่อให้ครอบคลุมทั้งเดือน
                $endDate = $endDate->endOfMonth();

                $currentDate = $startDate->copy()->startOfMonth();
                while ($currentDate->lte($endDate)) {
                    $months[] = [
                        'value' => $currentDate->format('M'),
                        'name' => $currentDate->locale('th')->monthName
                    ];
                    $currentDate->addMonth();
                }
            }

            return view('layouts.ta.subjectDetail', compact('courseTaClass', 'student', 'months'));
        } catch (\Exception $e) {
            Log::error('Error in showSubjectDetail: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาดในการแสดงข้อมูล');
        }
    }

    public function showTeachingData($id = null, Request $request)
    {
        try {
            if (!$id) {
                return redirect()->back()->with('error', 'กรุณาระบุ Class ID');
            }

            $selectedMonth = $request->query('month');
            DB::beginTransaction();

            // 1. Get regular teachings
            $apiTeachings = collect($this->tdbmService->getTeachings())
                ->filter(function ($teaching) use ($id) {
                    return $teaching['class_id'] == $id;
                });

            // 2. Get extra teachings
            $apiExtraTeachings = collect($this->tdbmService->getExtraTeachings())
                ->filter(function ($teaching) use ($id) {
                    return $teaching['class_id'] == $id;
                })
                ->map(function ($teaching) {
                    // แปลงรูปแบบให้ตรงกับ regular teaching
                    return [
                        'teaching_id' => $teaching['teaching_id'],
                        'start_time' => $teaching['class_date'] . ' ' . $teaching['start_time'],
                        'end_time' => $teaching['class_date'] . ' ' . $teaching['end_time'],
                        'duration' => $teaching['duration'],
                        'class_type' => 'E', // E for Extra
                        'status' => $teaching['status'],
                        'class_id' => $teaching['class_id'],
                        'teacher_id' => $teaching['teacher_id']
                    ];
                });

            // 3. Merge regular and extra teachings
            $allTeachings = $apiTeachings->concat($apiExtraTeachings);

            if ($allTeachings->isEmpty()) {
                session()->flash('info', 'ยังไม่มีข้อมูลการสอนสำหรับรายวิชานี้');
                return view('layouts.ta.teaching', ['teachings' => []]);
            }

            // 4. Get related API data
            $apiClasses = collect($this->tdbmService->getStudentClasses());
            $apiTeachers = collect($this->tdbmService->getTeachers());

            // 5. Save all teachings to local DB
            foreach ($allTeachings as $teaching) {
                Teaching::updateOrCreate(
                    ['teaching_id' => $teaching['teaching_id']],
                    [
                        'start_time' => $teaching['start_time'],
                        'end_time' => $teaching['end_time'],
                        'duration' => $teaching['duration'],
                        'class_type' => $teaching['class_type'] ?? 'N',
                        'status' => $teaching['status'] ?? 'W',
                        'class_id' => $teaching['class_id'],
                        'teacher_id' => $teaching['teacher_id']
                    ]
                );
            }

            // 6. Fetch data from local DB with filter
            $query = Teaching::with(['class', 'teacher', 'attendance'])
                ->where('class_id', $id);

            if ($selectedMonth) {
                $query->whereMonth('start_time', \Carbon\Carbon::parse("1-{$selectedMonth}-2024")->month);
            }

            $localTeachings = $query->orderBy('start_time', 'asc')
                ->get()
                ->map(function ($teaching) use ($apiClasses, $apiTeachers) {
                    $class = $apiClasses->firstWhere('class_id', $teaching->class_id);
                    $teacher = $apiTeachers->firstWhere('teacher_id', $teaching->teacher_id);

                    return (object)[
                        'id' => $teaching->teaching_id,
                        'start_time' => $teaching->start_time,
                        'end_time' => $teaching->end_time,
                        'duration' => $teaching->duration,
                        'class_type' => $teaching->class_type,
                        'class_id' => (object)[
                            'title' => $class['title'] ?? 'N/A',
                        ],
                        'teacher_id' => (object)[
                            'position' => $teacher['position'] ?? '',
                            'degree' => $teacher['degree'] ?? '',
                            'name' => $teacher['name'] ?? 'N/A',
                        ],
                        'attendance' => $teaching->attendance ? (object)[
                            'status' => $teaching->attendance->status,
                            'note' => $teaching->attendance->note ?? ''
                        ] : null
                    ];
                });

            DB::commit();

            return view('layouts.ta.teaching', [
                'teachings' => $localTeachings,
                'selectedMonth' => $selectedMonth
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in showTeachingData: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาดในการแสดงข้อมูลการสอน: ' . $e->getMessage());
        }
    }

    public function showAttendanceForm($teaching_id)
    {
        try {
            // ดึงข้อมูล teaching จาก local DB
            $teaching = Teaching::with(['attendance'])->findOrFail($teaching_id);

            // ถ้ามี attendance แล้วให้ redirect กลับ
            if ($teaching->attendance) {
                return redirect()
                    ->back()
                    ->with('error', 'คุณได้ลงเวลาการสอนไปแล้ว');
            }

            return view('layouts.ta.attendances', compact('teaching'));
        } catch (\Exception $e) {
            Log::error('Error in showAttendanceForm: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาดในการแสดงฟอร์มลงเวลา');
        }
    }

    public function submitAttendance(Request $request, $teaching_id)
    {
        try {
            DB::beginTransaction();

            // Validate request
            $request->validate([
                'status' => 'required|in:เข้าปฏิบัติการสอน,ลา',
                'note' => 'required|string|max:255',
            ], [
                'status.required' => 'กรุณาเลือกสถานะการเข้าสอน',
                'note.required' => 'กรุณากรอกงานที่ปฏิบัติ',
                'note.max' => 'งานที่ปฏิบัติต้องไม่เกิน 255 ตัวอักษร'
            ]);

            $user = Auth::user();
            $student = Students::where('user_id', $user->id)->firstOrFail();
            $teaching = Teaching::findOrFail($teaching_id);

            // ตรวจสอบว่ามีการลงเวลาไปแล้วหรือไม่
            if ($teaching->attendance) {
                return redirect()
                    ->back()
                    ->with('error', 'คุณได้ลงเวลาการสอนไปแล้ว');
            }

            // สร้าง attendance record
            Attendances::create([
                'status' => $request->status,
                'note' => $request->note,
                'teaching_id' => $teaching_id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'approve_at' => null,
                'approve_user_id' => null
            ]);

            DB::commit();

            // เก็บค่าเดือนที่ส่งมาจาก form
            $selectedMonth = $request->input('selected_month');

            // redirect กลับไปยังหน้า teaching พร้อมกับส่งค่าเดือนที่เลือกไว้
            return redirect()
                ->route('layout.ta.teaching', [
                    'id' => $teaching->class_id,
                    'month' => $selectedMonth
                ])
                ->with('success', 'บันทึกการลงเวลาสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in submitAttendance: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
    }
    public function storeExtraAttendance(Request $request)
    {
        try {
            // Validate the input
            $request->validate([
                'start_work' => 'required|date',
                'class_type' => 'required|string|in:L,C', // Match the accepted class types
                'detail' => 'required|string|max:255',
                'duration' => 'required|integer|min:1',
                'student_id' => 'required|exists:students,id',
                'class_id' => 'required|exists:classes,class_id',
            ]);

            // Begin transaction
            DB::beginTransaction();

            // Insert the record into extra_attendances
            ExtraAttendances::create([
                'start_work' => $request->start_work,
                'class_type' => $request->class_type,
                'detail' => $request->detail,
                'duration' => $request->duration,
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
            ]);

            // Commit the transaction
            DB::commit();

            // Redirect back with success message
            return redirect()->back()->with('success', 'บันทึกการลงเวลาเพิ่มเติมสำเร็จ');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error in storeExtraAttendance: ' . $e->getMessage());

            // Redirect back with error message and input data
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage())->withInput();
        }
    }
}
