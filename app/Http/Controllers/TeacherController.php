<?php

namespace App\Http\Controllers;

use App\Models\{
    Attendances,
    Classes,
    Courses,
    CourseTaClasses,
    CourseTas,
    ExtraAttendances,
    Requests,
    Students,
    Subjects,
    Teachers,
    Teaching,
    TeacherRequest,
    TeacherRequestsDetail,
    TeacherRequestStudent,
    Semesters,
    ExtraTeaching
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log, Hash};
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

    /**
     * แสดงฟอร์มเปลี่ยนรหัสผ่านของอาจารย์
     */
    public function showChangePasswordForm()
    {
        return view('layouts.teacher.change-password');
    }

    /**
     * ดำเนินการเปลี่ยนรหัสผ่านของอาจารย์
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'กรุณาระบุรหัสผ่านปัจจุบัน',
            'password.required' => 'กรุณาระบุรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        $user = \App\Models\User::find(Auth::id());

        // ตรวจสอบรหัสผ่านปัจจุบัน
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        // อัปเดตรหัสผ่านใหม่
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('teacher.home')->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ');
    }

    private function getActiveSemester()
    {
        // ลองดึงจาก session ก่อน
        $activeSemesterId = session('user_active_semester_id');

        // ถ้าไม่มีใน session ให้ดึงจากฐานข้อมูล
        if (!$activeSemesterId) {
            $setting = DB::table('setting_semesters')->where('key', 'user_active_semester_id')->first();

            if ($setting) {
                $activeSemesterId = $setting->value;
                session(['user_active_semester_id' => $activeSemesterId]);
            }
        }

        // ถ้ายังไม่มีค่า ให้ใช้ semester ล่าสุด
        if (!$activeSemesterId) {
            $semester = Semesters::orderBy('year', 'desc')
                ->orderBy('semesters', 'desc')
                ->first();
        } else {
            $semester = Semesters::find($activeSemesterId);
        }

        return $semester;
    }

    public function indexTARequests()
    {
        try {
            $teacher = Auth::user()->teacher;

            // หาเทอมปัจจุบัน
            $allSemesters = collect($this->tdbmService->getSemesters());
            $currentSemester = $allSemesters->sortByDesc('start_date')->first();

            if (!$currentSemester) {
                return back()->with('error', 'ไม่พบข้อมูลภาคการศึกษา');
            }

            $courses = Courses::where('owner_teacher_id', $teacher->teacher_id)
                ->where('semester_id', $currentSemester['semester_id']) // เพิ่มเงื่อนไขเทอมปัจจุบัน
                ->with([
                    'subjects',
                    'course_tas.student',
                    'course_tas.courseTaClasses.requests',
                    'teacherRequests'
                ])
                ->get()
                ->map(function ($course) {
                    $approvedTAs = $course->course_tas->filter(function ($ta) {
                        return $ta->courseTaClasses->flatMap->requests
                            ->where('status', 'A')
                            ->isNotEmpty();
                    });

                    $latestRequest = $course->teacherRequests
                        ->sortByDesc('created_at')
                        ->first();

                    return [
                        'course' => $course,
                        'approved_tas' => $approvedTAs,
                        'latest_request' => $latestRequest
                    ];
                });

            $requests = TeacherRequest::where('teacher_id', $teacher->teacher_id)
                ->whereIn('course_id', $courses->pluck('course.course_id'))
                ->with([
                    'details.students.courseTa.student',
                    'course.subjects'
                ])
                ->latest()
                ->get();

            return view('layouts.teacher.ta-request.index', compact('courses', 'requests'));
        } catch (\Exception $e) {
            Log::error('Error in indexTARequests: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function createTARequest($course_id)
    {
        $course = Courses::with(['subjects', 'course_tas.student'])->findOrFail($course_id);

        $availableStudents = Students::whereIn('id', function ($query) use ($course_id) {
            $query->select('student_id')
                ->from('course_tas')
                ->where('course_id', $course_id);
        })->get();

        Log::info('Course: ' . json_encode($course));
        Log::info('Available Students: ' . json_encode($availableStudents));

        return view('layouts.teacher.ta-request.create', compact('course', 'availableStudents'));
    }

    private function updatePendingRequestsCount()
    {
        $user = Auth::user();
        $teacher = Teachers::where('user_id', $user->id)->first();

        if ($teacher) {
            // ดึงข้อมูลภาคการศึกษาที่กำลังใช้งาน
            $activeSemester = $this->getActiveSemester();

            if ($activeSemester) {
                // นับคำขอที่รออนุมัติใหม่
                $pendingRequestsCount = CourseTas::whereHas('course', function ($query) use ($teacher, $activeSemester) {
                    $query->where('owner_teacher_id', $teacher->teacher_id)
                        ->where('semester_id', $activeSemester->semester_id);
                })
                    ->whereHas('courseTaClasses.requests', function ($query) {
                        $query->where('status', 'w'); // เป็นตัวพิมพ์เล็กตามที่ใช้ในระบบของคุณ
                    })
                    ->count();

                // บันทึกลงใน session เพื่อให้สามารถเข้าถึงได้จากทุกหน้า
                session(['pendingRequestsCount' => $pendingRequestsCount]);
            }
        }
    }

    public function storeTARequest(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,course_id',
            'payment_type' => 'required|in:lecture,lab,both',
            'students' => 'required|array|min:1',
            'students.*.course_ta_id' => 'required|exists:course_tas,id',
            'students.*.teaching_hours' => 'required|integer|min:0',
            'students.*.prep_hours' => 'required|integer|min:0',
            'students.*.grading_hours' => 'required|integer|min:0',
            'students.*.other_hours' => 'nullable|integer|min:0',
            'students.*.other_duties' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // สร้างคำร้องหลัก
            $teacherRequest = TeacherRequest::create([
                'teacher_id' => Auth::user()->teacher->teacher_id,
                'course_id' => $validated['course_id'],
                'status' => 'W',
                'payment_type' => $validated['payment_type']
            ]);

            // สร้างรายละเอียดกลุ่ม
            $requestDetail = TeacherRequestsDetail::create([
                'teacher_request_id' => $teacherRequest->id,
                'group_number' => 1,
                'undergrad_count' => 0,
                'graduate_count' => count($validated['students'])
            ]);

            // เพิ่มข้อมูล TA แต่ละคน
            foreach ($validated['students'] as $studentData) {
                $totalHours =
                    $studentData['teaching_hours'] +
                    $studentData['prep_hours'] +
                    $studentData['grading_hours'] +
                    ($studentData['other_hours'] ?? 0);

                TeacherRequestStudent::create([
                    'teacher_requests_detail_id' => $requestDetail->id,
                    'course_ta_id' => $studentData['course_ta_id'],
                    'teaching_hours' => $studentData['teaching_hours'],
                    'prep_hours' => $studentData['prep_hours'],
                    'grading_hours' => $studentData['grading_hours'],
                    'other_hours' => $studentData['other_hours'] ?? 0,
                    'other_duties' => $studentData['other_duties'] ?? null,
                    'total_hours_per_week' => $totalHours
                ]);
            }
            $this->updatePendingRequestsCount();
            DB::commit();
            return redirect()->route('teacher.ta-requests.index')
                ->with('success', 'บันทึกคำร้องขอ TA สำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function edit($id)
    {
        try {
            $request = TeacherRequest::with([
                'details.students.courseTa.student',
                'course.subjects',
                'teacher'
            ])->findOrFail($id);

            // ตรวจสอบว่าเป็นคำร้องของอาจารย์ท่านนี้จริงๆ
            if ($request->teacher_id !== Auth::user()->teacher->teacher_id) {
                return redirect()->route('teacher.ta-requests.index')
                    ->with('error', 'ไม่มีสิทธิ์เข้าถึงคำร้องนี้');
            }

            // ตรวจสอบว่าสถานะยังเป็นรอดำเนินการอยู่
            if ($request->status !== 'W') {
                return redirect()->route('teacher.ta-requests.show', $id)
                    ->with('error', 'ไม่สามารถแก้ไขคำร้องที่ดำเนินการไปแล้ว');
            }

            return view('layouts.teacher.ta-request.edit', compact('request'));
        } catch (\Exception $e) {
            Log::error('Error in edit TA request: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:lecture,lab,both',
            'students' => 'required|array|min:1',
            'students.*.course_ta_id' => 'required|exists:course_tas,id',
            'students.*.teaching_hours' => 'required|integer|min:0',
            'students.*.prep_hours' => 'required|integer|min:0',
            'students.*.grading_hours' => 'required|integer|min:0',
            'students.*.other_hours' => 'nullable|integer|min:0',
            'students.*.other_duties' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $teacherRequest = TeacherRequest::with('details.students')
                ->findOrFail($id);

            // ตรวจสอบสิทธิ์และสถานะ
            if ($teacherRequest->teacher_id !== Auth::user()->teacher->teacher_id) {
                throw new \Exception('ไม่มีสิทธิ์แก้ไขคำร้องนี้');
            }

            if ($teacherRequest->status !== 'W') {
                throw new \Exception('ไม่สามารถแก้ไขคำร้องที่ดำเนินการไปแล้ว');
            }

            // อัพเดตข้อมูลหลัก
            $teacherRequest->update([
                'payment_type' => $validated['payment_type']
            ]);

            // อัพเดตข้อมูล TA แต่ละคน
            foreach ($validated['students'] as $studentData) {
                $totalHours =
                    $studentData['teaching_hours'] +
                    $studentData['prep_hours'] +
                    $studentData['grading_hours'] +
                    ($studentData['other_hours'] ?? 0);

                TeacherRequestStudent::where('course_ta_id', $studentData['course_ta_id'])
                    ->whereIn('teacher_requests_detail_id', $teacherRequest->details->pluck('id'))
                    ->update([
                        'teaching_hours' => $studentData['teaching_hours'],
                        'prep_hours' => $studentData['prep_hours'],
                        'grading_hours' => $studentData['grading_hours'],
                        'other_hours' => $studentData['other_hours'] ?? 0,
                        'other_duties' => $studentData['other_duties'] ?? null,
                        'total_hours_per_week' => $totalHours
                    ]);
            }

            DB::commit();
            return redirect()->route('teacher.ta-requests.show', $id)
                ->with('success', 'อัพเดตคำร้องสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating TA request: ' . $e->getMessage());
            return back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function showTARequest($id)
    {
        try {
            // ดึงข้อมูลคำร้อง
            $request = TeacherRequest::findOrFail($id);

            // ดึงข้อมูลอาจารย์
            $teacher = Teachers::where('teacher_id', $request->teacher_id)->first();

            // ดึงข้อมูลรายวิชา
            $course = Courses::with('subjects')->where('course_id', $request->course_id)->first();

            // ดึงข้อมูลรายละเอียด
            $details = TeacherRequestsDetail::where('teacher_request_id', $id)
                ->with(['students.courseTa.student'])
                ->get();

            return view('layouts.teacher.ta-request.show', compact('request', 'teacher', 'course', 'details'));
        } catch (\Exception $e) {
            Log::error('Error in showTARequest: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }
    public function subject()
    {
        $subjects = Subjects::all();
        return view('layouts.teacher.subject', compact('subjects'));
    }

    public function subjectDetail($course_id)
    {
        try {
            // ดึงข้อมูลภาคการศึกษาที่กำหนดให้เห็น
            $activeSemester = $this->getActiveSemester();

            if (!$activeSemester) {
                return back()->with('error', 'ไม่พบข้อมูลภาคการศึกษาที่กำหนดให้แสดง');
            }

            // ดึงข้อมูลคอร์สจากฐานข้อมูลโดยตรง พร้อมความสัมพันธ์ที่เกี่ยวข้อง
            $course = Courses::with(['subjects', 'teachers', 'semesters', 'curriculums'])
                ->where('course_id', $course_id)
                ->where('semester_id', $activeSemester->semester_id)
                ->first();

            if (!$course) {
                return back()->with('error', 'ไม่พบข้อมูลรายวิชา หรือรายวิชาไม่อยู่ในภาคการศึกษาที่กำหนด');
            }

            // จัดเตรียมข้อมูลในรูปแบบที่ต้องการใช้ในหน้า view
            $formattedCourse = [
                'course_id' => $course->course_id,
                'subject_id' => $course->subject_id,
                'semester_id' => $course->semester_id,
                'owner_teacher_id' => $course->owner_teacher_id,
                'status' => $course->status,
                'subject' => [
                    'subject_id' => $course->subjects->subject_id ?? 'N/A',
                    'name_en' => $course->subjects->name_en ?? 'N/A',
                    'name_th' => $course->subjects->name_th ?? 'N/A',
                ],
                'teacher' => [
                    'teacher_id' => $course->teachers->teacher_id ?? 'N/A',
                    'name' => $course->teachers->name ?? 'N/A',
                    'title_th' => $course->teachers->title_th ?? '',
                    'lastname_th' => $course->teachers->lastname_th ?? '',
                    'position' => $course->teachers->position ?? '',
                    'degree' => $course->teachers->degree ?? '',
                ],
                'current_semester' => [
                    'semester' => $activeSemester->semesters,
                    'year' => $activeSemester->year,
                    'start_date' => $activeSemester->start_date,
                    'end_date' => $activeSemester->end_date,
                ],
                'curriculum' => [
                    'name_th' => $course->curriculums->name_th ?? 'N/A',
                    'name_en' => $course->curriculums->name_en ?? 'N/A',
                ]
            ];

            // ดึงข้อมูลผู้ช่วยสอนที่เกี่ยวข้องกับคอร์สนี้
            $teachingAssistants = CourseTas::with(['student', 'courseTaClasses.requests'])
                ->where('course_id', $course_id)
                ->get()
                ->map(function ($ta) {
                    $latestRequest = $ta->courseTaClasses
                        ->flatMap->requests
                        ->sortByDesc('created_at')
                        ->first();

                    return [
                        'id' => $ta->id,
                        'name' => $ta->student->name ?? 'N/A',
                        'email' => $ta->student->email ?? 'N/A',
                        'student_id' => $ta->student->student_id ?? 'N/A',
                        'status' => $latestRequest ? strtolower($latestRequest->status) : 'w'
                    ];
                });

            $formattedCourse['teaching_assistants'] = $teachingAssistants;

            return view('layouts.teacher.subjectDetail', [
                'course' => $formattedCourse,
                'currentSemester' => $activeSemester
            ]);
        } catch (\Exception $e) {
            Log::error('Error in subjectDetail: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function taDetail($ta_id)
    {
        try {
            // ดึงข้อมูล CourseTa พร้อมความสัมพันธ์
            $ta = CourseTas::with(['student', 'course'])->findOrFail($ta_id);
            $student = $ta->student;
            $currentTeacher = Auth::user()->teacher;

            // ดึงข้อมูลภาคการศึกษาที่กำหนดให้เห็น
            $activeSemester = $this->getActiveSemester();

            if (!$activeSemester) {
                return back()->with('error', 'ไม่พบข้อมูลภาคการศึกษาที่กำหนดให้แสดง');
            }

            // ตรวจสอบว่า course อยู่ในภาคการศึกษาที่กำหนดหรือไม่
            if ($ta->course->semester_id != $activeSemester->semester_id) {
                return back()->with('error', 'รายวิชานี้ไม่อยู่ในภาคการศึกษาที่กำหนดให้แสดง');
            }

            $semester = $activeSemester; // ใช้ activeSemester แทน

            $start = \Carbon\Carbon::parse($semester->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($semester->end_date)->endOfDay();

            // สร้างรายการเดือน
            $monthsInSemester = [];
            if ($start->year === $end->year) {
                for ($m = $start->month; $m <= $end->month; $m++) {
                    $date = \Carbon\Carbon::createFromDate($start->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
            } else {
                for ($m = $start->month; $m <= 12; $m++) {
                    $date = \Carbon\Carbon::createFromDate($start->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
                for ($m = 1; $m <= $end->month; $m++) {
                    $date = \Carbon\Carbon::createFromDate($end->year, $m, 1);
                    $monthsInSemester[$date->format('Y-m')] = $date->locale('th')->monthName . ' ' . ($date->year + 543);
                }
            }

            $selectedYearMonth = request('month', $start->format('Y-m'));
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            // ดึงข้อมูลการลงเวลาทั้งหมดของนักศึกษาที่เลือก
            $attendances = Attendances::where('student_id', $student->id)->get();

            // แยกการลงเวลาปกติและการลงเวลาการสอนชดเชย
            $regularAttendances = $attendances->where('is_extra', false)->where('extra_teaching_id', null);
            $extraTeachingAttendances = $attendances->where('is_extra', true)->where('extra_teaching_id', '!=', null);

            // เตรียม teaching_ids สำหรับการลงเวลาปกติ
            $teachingIds = $regularAttendances->pluck('teaching_id')->toArray();

            // ดึงรายวิชาที่อาจารย์คนปัจจุบันเป็นเจ้าของในภาคการศึกษาที่กำหนด
            $coursesOwnedByTeacher = Courses::where('owner_teacher_id', $currentTeacher->teacher_id)
                ->where('semester_id', $activeSemester->semester_id) // กรองตามภาคการศึกษาที่กำหนด
                ->pluck('course_id')
                ->toArray();

            // ดึงข้อมูล Class ที่อยู่ในรายวิชาที่อาจารย์เป็นเจ้าของ
            $classesInTeacherCourses = Classes::whereIn('course_id', $coursesOwnedByTeacher)
                ->pluck('class_id')
                ->toArray();

            // ดึงข้อมูลการสอนตาม teaching_ids ที่ได้
            $teachings = Teaching::with(['teacher', 'class'])
                ->whereIn('teaching_id', $teachingIds)
                ->whereIn('class_id', $classesInTeacherCourses)
                ->get();

            // กรองตามเดือนที่เลือกโดยใช้ start_time ของ teaching
            $filteredTeachings = $teachings->filter(function ($teaching) use ($selectedDate) {
                return \Carbon\Carbon::parse($teaching->start_time)->format('Y-m') === $selectedDate->format('Y-m');
            });

            // สร้าง collection ใหม่ที่มี attendance ของนักศึกษาที่เลือกเท่านั้น (สำหรับการสอนปกติ)
            $formattedTeachings = collect();

            foreach ($filteredTeachings as $teaching) {
                $attendance = $regularAttendances->where('teaching_id', $teaching->teaching_id)->first();
                if ($attendance) {
                    $teachingWithAttendance = clone $teaching;
                    $teachingWithAttendance->attendance = $attendance;
                    $formattedTeachings->push($teachingWithAttendance);
                }
            }

            // ดึงข้อมูลการสอนชดเชย (ExtraTeaching)
            $extraTeachingIds = $extraTeachingAttendances->pluck('extra_teaching_id')->toArray();
            $extraTeachings = ExtraTeaching::with(['teacher', 'class'])
                ->whereIn('extra_class_id', $extraTeachingIds)
                ->whereIn('class_id', $classesInTeacherCourses)
                ->get();

            // กรองตามเดือนที่เลือก
            $filteredExtraTeachings = $extraTeachings->filter(function ($extraTeaching) use ($selectedDate) {
                return \Carbon\Carbon::parse($extraTeaching->class_date)->format('Y-m') === $selectedDate->format('Y-m');
            });

            // เพิ่มข้อมูล attendance เข้าไปใน extraTeaching objects
            foreach ($filteredExtraTeachings as $extraTeaching) {
                $attendance = $extraTeachingAttendances->where('extra_teaching_id', $extraTeaching->extra_class_id)->first();
                if ($attendance) {
                    $extraTeaching->attendance = $attendance;

                    // แปลงให้อยู่ในรูปแบบเดียวกับ regular teaching
                    $convertedTeaching = new \stdClass();
                    $convertedTeaching->teaching_id = $extraTeaching->extra_class_id;
                    $convertedTeaching->start_time = $extraTeaching->class_date . ' ' . $extraTeaching->start_time;
                    $convertedTeaching->end_time = $extraTeaching->class_date . ' ' . $extraTeaching->end_time;
                    $convertedTeaching->duration = $extraTeaching->duration;
                    $convertedTeaching->class_type = 'E'; // กำหนดเป็น E สำหรับ extra teaching
                    $convertedTeaching->teacher = $extraTeaching->teacher;
                    $convertedTeaching->class = $extraTeaching->class;
                    $convertedTeaching->attendance = $attendance;

                    $formattedTeachings->push($convertedTeaching);
                }
            }

            // ดึงข้อมูลการลงเวลาพิเศษเฉพาะของนักศึกษาที่เลือก และของหลักสูตรที่อาจารย์ปัจจุบันสอน
            $extraAttendances = ExtraAttendances::where('student_id', $student->id)
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->whereIn('class_id', $classesInTeacherCourses)
                ->with([
                    'classes' => function ($query) {
                        $query->with('teachers');
                    }
                ])
                ->get();

            // ตรวจสอบสถานะการอนุมัติ - แก้ไขส่วนนี้ให้ตรวจสอบแยกตามอาจารย์/วิชา
            $normalAttendanceApproved = Attendances::where('student_id', $student->id)
                ->whereYear('created_at', $selectedDate->year)
                ->whereMonth('created_at', $selectedDate->month)
                ->where('approve_status', 'a')
                ->whereHas('teaching', function ($query) use ($classesInTeacherCourses) {
                    $query->whereIn('class_id', $classesInTeacherCourses);
                })
                ->exists();

            $extraTeachingApproved = Attendances::where('student_id', $student->id)
                ->whereYear('created_at', $selectedDate->year)
                ->whereMonth('created_at', $selectedDate->month)
                ->where('approve_status', 'a')
                ->where('is_extra', true)
                ->whereHas('extraTeaching', function ($query) use ($classesInTeacherCourses) {
                    $query->whereIn('class_id', $classesInTeacherCourses);
                })
                ->exists();

            $extraAttendanceApproved = ExtraAttendances::where('student_id', $student->id)
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->where('approve_status', 'a')
                ->whereIn('class_id', $classesInTeacherCourses)
                ->exists();

            // เปลี่ยนเป็นตรวจสอบเฉพาะรายการของอาจารย์ปัจจุบันเท่านั้น
            $isMonthApproved = $normalAttendanceApproved || $extraTeachingApproved || $extraAttendanceApproved;
            $approvalNote = null;

            if ($isMonthApproved) {
                // ดึงหมายเหตุการอนุมัติล่าสุดเฉพาะของอาจารย์คนปัจจุบัน
                $latestNormalApproval = Attendances::where('student_id', $student->id)
                    ->whereYear('created_at', $selectedDate->year)
                    ->whereMonth('created_at', $selectedDate->month)
                    ->where('approve_status', 'a')
                    ->where('approve_user_id', auth()->id())
                    ->whereHas('teaching', function ($query) use ($classesInTeacherCourses) {
                        $query->whereIn('class_id', $classesInTeacherCourses);
                    })
                    ->latest()
                    ->first();

                $latestExtraApproval = ExtraAttendances::where('student_id', $student->id)
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->where('approve_status', 'a')
                    ->where('approve_user_id', auth()->id())
                    ->whereIn('class_id', $classesInTeacherCourses)
                    ->latest()
                    ->first();

                if ($latestNormalApproval && $latestExtraApproval) {
                    $approvalNote = $latestNormalApproval->approve_at > $latestExtraApproval->approve_at
                        ? $latestNormalApproval->approve_note
                        : $latestExtraApproval->approve_note;
                } elseif ($latestNormalApproval) {
                    $approvalNote = $latestNormalApproval->approve_note;
                } elseif ($latestExtraApproval) {
                    $approvalNote = $latestExtraApproval->approve_note;
                }
            }

            // ใช้ formattedTeachings แทน teachings
            $teachings = $formattedTeachings;
            $pendingAttendancesCount = $this->countPendingAttendances();
            session(['pendingAttendancesCount' => $pendingAttendancesCount]);

            return view('layouts.teacher.taDetail', compact(
                'student',
                'semester',
                'teachings',
                'extraAttendances',
                'monthsInSemester',
                'selectedYearMonth',
                'isMonthApproved',
                'approvalNote',
                'activeSemester',
                'pendingAttendancesCount'
            ));
        } catch (\Exception $e) {
            Log::error('Exception in taDetail: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function approveMonthlyAttendance(Request $request, $ta_id)
    {
        try {
            $yearMonth = $request->input('year_month');
            $approveNote = $request->input('approve_note');
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);
            $ta = CourseTas::with('student')->findOrFail($ta_id);
            $currentTeacher = Auth::user()->teacher;

            // รับค่า checkbox ที่เลือก
            $normalAttendanceIds = $request->input('normal_attendances', []);
            $extraAttendanceIds = $request->input('extra_attendances', []);

            // ตรวจสอบว่ามีการเลือกรายการหรือไม่
            if (empty($normalAttendanceIds) && empty($extraAttendanceIds)) {
                return back()->with('error', 'กรุณาเลือกรายการที่ต้องการอนุมัติ');
            }

            // ดึงรายวิชาที่อาจารย์คนปัจจุบันเป็นเจ้าของ
            $coursesOwnedByTeacher = Courses::where('owner_teacher_id', $currentTeacher->teacher_id)
                ->pluck('course_id')->toArray();

            // ดึงข้อมูล Class ที่อยู่ในรายวิชาที่อาจารย์เป็นเจ้าของ
            $classesInTeacherCourses = Classes::whereIn('course_id', $coursesOwnedByTeacher)
                ->pluck('class_id')->toArray();

            DB::beginTransaction();

            try {
                $normalCount = 0;
                $extraTeachingCount = 0;
                $extraAttendanceCount = 0;

                // อนุมัติการลงเวลาปกติและสอนชดเชย (Teaching ปกติและ Extra Teaching)
                foreach ($normalAttendanceIds as $id) {
                    // ตรวจสอบว่าเป็น ID ที่มีรูปแบบ "extra-XXX" หรือไม่
                    if (strpos($id, 'extra-') === 0) {
                        // กรณี Extra Teaching จากรูปแบบเก่า (มี prefix)
                        $extraTeachingId = str_replace('extra-', '', $id);

                        $this->processExtraTeaching($extraTeachingId, $ta->student_id, $classesInTeacherCourses, $approveNote, $extraTeachingCount);
                    } else {
                        // ตรวจสอบว่าเป็น Extra Teaching หรือ Regular Teaching
                        $extraTeaching = ExtraTeaching::where('extra_class_id', $id)
                            ->whereIn('class_id', $classesInTeacherCourses)
                            ->first();

                        if ($extraTeaching) {
                            // กรณี Extra Teaching จากรูปแบบใหม่ (ไม่มี prefix)
                            $this->processExtraTeaching($id, $ta->student_id, $classesInTeacherCourses, $approveNote, $extraTeachingCount);
                        } else {
                            // กรณี Teaching ปกติ
                            $teaching = Teaching::where('teaching_id', $id)
                                ->whereIn('class_id', $classesInTeacherCourses)
                                ->first();

                            if (!$teaching) {
                                continue; // ข้ามรายการที่ไม่เกี่ยวข้องกับอาจารย์คนปัจจุบัน
                            }

                            $attendance = Attendances::where('teaching_id', $id)
                                ->where('student_id', $ta->student_id)
                                ->where(function ($query) {
                                    $query->whereNull('approve_status')
                                        ->orWhere('approve_status', '!=', 'a');
                                })
                                ->first();

                            if ($attendance) {
                                $attendance->update([
                                    'approve_status' => 'a',
                                    'approve_at' => now(),
                                    'approve_user_id' => auth()->id(),
                                    'approve_note' => $approveNote
                                ]);
                                $normalCount++;
                            }
                        }
                    }
                }

                // อนุมัติการลงเวลาพิเศษ (Extra Attendances)
                foreach ($extraAttendanceIds as $id) {
                    $extraAttendance = ExtraAttendances::where('id', $id)
                        ->where('student_id', $ta->student_id)
                        ->whereIn('class_id', $classesInTeacherCourses) // เฉพาะรายการของอาจารย์คนปัจจุบัน
                        ->where(function ($query) {
                            $query->whereNull('approve_status')
                                ->orWhere('approve_status', '!=', 'a');
                        })
                        ->first();

                    if ($extraAttendance) {
                        $extraAttendance->update([
                            'approve_status' => 'a',
                            'approve_at' => now(),
                            'approve_user_id' => auth()->id(),
                            'approve_note' => $approveNote
                        ]);
                        $extraAttendanceCount++;
                    }
                }

                // ตรวจสอบว่ามีการอนุมัติข้อมูลหรือไม่
                $totalApproved = $normalCount + $extraTeachingCount + $extraAttendanceCount;

                if ($totalApproved == 0) {
                    DB::rollBack();
                    return back()->with('error', 'ไม่สามารถอนุมัติรายการที่เลือกได้');
                }

                // บันทึกข้อมูลลงฐานข้อมูล
                DB::commit();

                // คำนวณจำนวนรายการที่อนุมัติแต่ละประเภท
                $message = "อนุมัติรายการที่เลือกเรียบร้อยแล้ว ";
                $message .= "(ปกติ: {$normalCount}, สอนชดเชย: {$extraTeachingCount}, งานพิเศษ: {$extraAttendanceCount})";
                $pendingAttendancesCount = $this->countPendingAttendances();
                session(['pendingAttendancesCount' => $pendingAttendancesCount]);
                return back()->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during approval: ' . $e->getMessage());
                return back()->with('error', 'เกิดข้อผิดพลาดในการอนุมัติ: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการประมวลผล: ' . $e->getMessage());
        }
    }

    // เพิ่มเมธอดช่วยสำหรับการประมวลผล Extra Teaching
    private function processExtraTeaching($extraTeachingId, $studentId, $classesInTeacherCourses, $approveNote, &$extraTeachingCount)
    {
        // ตรวจสอบว่า extra teaching นี้อยู่ในคลาสของอาจารย์คนปัจจุบันหรือไม่
        $classCheck = ExtraTeaching::where('extra_class_id', $extraTeachingId)
            ->whereIn('class_id', $classesInTeacherCourses)
            ->exists();

        if (!$classCheck) {
            return; // ข้ามรายการที่ไม่เกี่ยวข้องกับอาจารย์คนปัจจุบัน
        }

        $attendance = Attendances::where('extra_teaching_id', $extraTeachingId)
            ->where('student_id', $studentId)
            ->where('is_extra', true)
            ->where(function ($query) {
                $query->whereNull('approve_status')
                    ->orWhere('approve_status', '!=', 'a');
            })
            ->first();

        if ($attendance) {
            $attendance->update([
                'approve_status' => 'a',
                'approve_at' => now(),
                'approve_user_id' => auth()->id(),
                'approve_note' => $approveNote
            ]);
            $extraTeachingCount++;
        }
    }

    public function subjectTeacher()
    {
        try {
            $user = Auth::user();
            $teacher = Teachers::where('user_id', $user->id)->first();

            // ดึงข้อมูลภาคการศึกษาที่กำหนดให้เห็น
            $activeSemester = $this->getActiveSemester();

            if (!$activeSemester) {
                return view('layouts.teacher.subject', ['subjects' => [], 'currentSemester' => null])
                    ->with('error', 'ไม่พบข้อมูลภาคการศึกษาที่กำหนดให้แสดง');
            }

            // ดึงข้อมูลรายวิชาที่อาจารย์สอนในภาคการศึกษาที่กำหนด จากฐานข้อมูลโดยตรง
            $teacherCourses = Courses::where('owner_teacher_id', $teacher->teacher_id)
                ->where('semester_id', $activeSemester->semester_id)
                ->where('status', 'A')
                ->with('subjects') // eager load ข้อมูล subjects เพื่อไม่ต้องดึงข้อมูลซ้ำๆ
                ->get();

            // ดึงจำนวน TA ในแต่ละคอร์ส
            $subjectsWithTAs = CourseTas::whereIn('course_id', $teacherCourses->pluck('course_id'))
                ->select('course_id', DB::raw('COUNT(DISTINCT student_id) as ta_count'))
                ->groupBy('course_id')
                ->get();

            $subjects = [];
            foreach ($subjectsWithTAs as $courseTa) {
                $course = $teacherCourses->firstWhere('course_id', $courseTa->course_id);
                if (!$course || !$course->subjects) {
                    continue;
                }
                $classIds = Classes::where('course_id', $course->course_id)->pluck('class_id')->toArray();
                $pendingAttendances = Attendances::whereHas('teaching', function ($query) use ($classIds) {
                    $query->whereIn('class_id', $classIds);
                })
                    ->where(function ($query) {
                        $query->whereNull('approve_status')
                            ->orWhere('approve_status', '!=', 'a');
                    })
                    ->count();

                $pendingExtraAttendances = ExtraAttendances::whereIn('class_id', $classIds)
                    ->where(function ($query) {
                        $query->whereNull('approve_status')
                            ->orWhere('approve_status', '!=', 'a');
                    })
                    ->count();

                $totalPending = $pendingAttendances + $pendingExtraAttendances;
                $subject = $course->subjects;
                $subjectId = $subject->subject_id;

                if (!isset($subjects[$subjectId])) {
                    $subjects[$subjectId] = [
                        'subject_id' => $subjectId,
                        'name_en' => $subject->name_en,
                        'ta_count' => $courseTa->ta_count,
                        'pending_attendances' => $totalPending,
                        'courses' => [$course]
                    ];
                } else {
                    $subjects[$subjectId]['ta_count'] += $courseTa->ta_count;
                    $subjects[$subjectId]['pending_attendances'] += $totalPending;
                    $subjects[$subjectId]['courses'][] = $course;
                }
            }
            $pendingAttendancesCount = $this->countPendingAttendances();
            session(['pendingAttendancesCount' => $pendingAttendancesCount]);
            return view('layouts.teacher.subject', [
                'subjects' => array_values($subjects),
                'currentSemester' => $activeSemester,
                'pendingAttendancesCount' => $pendingAttendancesCount
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return view('layouts.teacher.subject', ['subjects' => []])
                ->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function countPendingRequests($teacherId, $semesterId)
    {
        return CourseTas::whereHas('course', function ($query) use ($semesterId) {
            $query->where('semester_id', $semesterId);
        })
            ->whereHas('course', function ($query) use ($teacherId) {
                $query->where('owner_teacher_id', $teacherId);
            })
            ->whereHas('courseTaClasses.requests', function ($query) {
                $query->where('status', 'W');
            })
            ->count();
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
            // ดึงข้อมูลภาคการศึกษาที่กำหนดให้เห็น
            $activeSemester = $this->getActiveSemester();

            if (!$activeSemester) {
                return redirect()->back()->with('error', 'ไม่พบข้อมูลภาคการศึกษาที่กำหนดให้แสดง');
            }

            $apiCourses = collect($tdbmApiService->getCourses());
            $apiSubjects = collect($tdbmApiService->getSubjects());

            $teacherCourseIds = $apiCourses
                ->where('owner_teacher_id', (string) $teacher->teacher_id)
                ->where('status', 'A')
                // กรองเฉพาะคอร์สในภาคการศึกษาที่กำหนด
                ->where('semester_id', (string) $activeSemester->semester_id)
                ->pluck('course_id')
                ->toArray();

            // ดึงข้อมูล CourseTas ที่อยู่ในคอร์สของอาจารย์และอยู่ในภาคการศึกษาที่กำหนดเท่านั้น
            $courseTas = CourseTas::with(['student', 'courseTaClasses.requests', 'course'])
                ->whereIn('course_id', $teacherCourseIds)
                ->whereHas('course', function ($query) use ($activeSemester) {
                    $query->where('semester_id', $activeSemester->semester_id);
                })
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
                    'status' => $latestRequest ? strtolower($latestRequest->status) : 'w',
                    'approved_at' => $latestRequest ? $latestRequest->approved_at : null,
                    'comment' => $latestRequest ? $latestRequest->comment : '',
                ];
            })->filter(); // กรองค่า null ออก

            $pendingRequestsCount = $formattedCourseTas->where('status', 'w')->count();
            session(['pendingRequestsCount' => $pendingRequestsCount]);


            Log::info('Formatted Course TAs count: ' . $formattedCourseTas->count());

            return view('layouts.teacher.teacherHome', [
                'courseTas' => $formattedCourseTas,
                'currentSemester' => $activeSemester,
                'pendingRequestsCount' => $pendingRequestsCount

            ]);
        } catch (\Exception $e) {
            Log::error('Error in showTARequests: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function updateTARequestStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $courseTaIds = $request->input('course_ta_ids', []);
            $statuses = $request->input('statuses', []);
            $comments = $request->input('comments', []);

            foreach ($courseTaIds as $index => $courseTaId) {
                $courseTa = CourseTas::findOrFail($courseTaId);

                // ตรวจสอบสถานะปัจจุบันของคำร้อง
                $courseTaClasses = CourseTaClasses::where('course_ta_id', $courseTaId)->get();
                $latestRequest = null;

                // หาคำร้องล่าสุดเพื่อตรวจสอบสถานะปัจจุบัน
                foreach ($courseTaClasses as $class) {
                    $request = Requests::where('course_ta_class_id', $class->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($request && (!$latestRequest || $request->created_at > $latestRequest->created_at)) {
                        $latestRequest = $request;
                    }
                }

                // ถ้าสถานะปัจจุบันเป็น 'A' 
                if ($latestRequest && strtoupper($latestRequest->status) === 'A') {
                    continue;
                }

                // ถ้าไม่มี courseTaClasses ให้สร้างใหม่
                if ($courseTaClasses->isEmpty()) {
                    $courseTaClass = CourseTaClasses::create([
                        'course_ta_id' => $courseTaId,
                        'class_id' => $courseTa->course_id
                    ]);
                    $courseTaClasses = collect([$courseTaClass]);
                }

                // อัปเดตสถานะสำหรับทุก class
                foreach ($courseTaClasses as $courseTaClass) {
                    Requests::updateOrCreate(
                        ['course_ta_class_id' => $courseTaClass->id],
                        [
                            'status' => strtoupper($statuses[$index]),
                            'comment' => $comments[$index] ?? null,
                            'approved_at' => now(),
                        ]
                    );
                }
            }
            $this->updatePendingRequestsCount();
            DB::commit();
            return redirect()->back()->with('success', 'อัพเดทสถานะสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating TA request status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการอัพเดทสถานะ: ' . $e->getMessage());
        }
    }

    private function countPendingAttendances()
    {
        $user = Auth::user();
        $teacher = Teachers::where('user_id', $user->id)->first();

        if (!$teacher) {
            return 0;
        }

        $activeSemester = $this->getActiveSemester();
        if (!$activeSemester) {
            return 0;
        }

        // ดึงรายวิชาที่อาจารย์คนปัจจุบันเป็นเจ้าของ
        $coursesOwnedByTeacher = Courses::where('owner_teacher_id', $teacher->teacher_id)
            ->where('semester_id', $activeSemester->semester_id)
            ->pluck('course_id')
            ->toArray();

        // ดึงข้อมูล Class ที่อยู่ในรายวิชาที่อาจารย์เป็นเจ้าของ
        $classesInTeacherCourses = Classes::whereIn('course_id', $coursesOwnedByTeacher)
            ->pluck('class_id')
            ->toArray();

        // นับการลงเวลาปกติที่รออนุมัติ
        $pendingAttendances = Attendances::whereHas('teaching', function ($query) use ($classesInTeacherCourses) {
            $query->whereIn('class_id', $classesInTeacherCourses);
        })
            ->where(function ($query) {
                $query->whereNull('approve_status')
                    ->orWhere('approve_status', '!=', 'a');
            })
            ->count();

        // นับการลงเวลาพิเศษที่รออนุมัติ
        $pendingExtraAttendances = ExtraAttendances::whereIn('class_id', $classesInTeacherCourses)
            ->where(function ($query) {
                $query->whereNull('approve_status')
                    ->orWhere('approve_status', '!=', 'a');
            })
            ->count();

        return $pendingAttendances + $pendingExtraAttendances;
    }
}
