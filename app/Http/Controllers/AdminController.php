<?php

namespace App\Http\Controllers;

use App\Models\{
    Announce,
    Classes,
    Courses,
    CourseTas,
    CourseTaClasses,
    Disbursements,
    ExtraAttendances,
    Requests,
    Students,
    Teaching,
    TeacherRequest,
    TeacherRequestsDetail,
    CompensationRate,
    CompensationTransaction,
    Semesters,
    ExtraTeaching,
    Attendances,
    CourseBudget
};
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TaAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use PhpOffice\PhpSpreadsheet\IOFactory;



use Illuminate\Http\Request;

class AdminController extends Controller
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

    public function adminHome()
    {
        $coursesWithTAs = Courses::whereHas('course_tas.courseTaClasses.requests', function ($query) {
            $query->where('status', 'A')
                ->whereNotNull('approved_at');
        })
            ->with([
                'subjects',
                'teachers',
                'course_tas.student',
                'course_tas.courseTaClasses.requests' => function ($query) {
                    $query->where('status', 'A')
                        ->whereNotNull('approved_at');
                }
            ])
            ->get();

        // return view('layouts.admin.taUsers', compact('coursesWithTAs'));

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
            });
        return view('layouts.admin.adminHome', compact('requests'));
    }

    public function taUsers(Request $request)
    {
        try {
            // ดึงข้อมูล semesters ทั้งหมดเพื่อให้ admin เลือก
            $semesters = Semesters::orderBy('year', 'desc')
                ->orderBy('semesters', 'desc')
                ->get();

            // ดึง semester_id ที่ admin เลือกจาก request หรือใช้ค่าที่บันทึกใน session
            $selectedSemesterId = $request->input('semester_id');

            if ($selectedSemesterId) {
                // บันทึกค่าที่เลือกลงใน session สำหรับ admin
                session(['admin_selected_semester_id' => $selectedSemesterId]);
            } else {
                $selectedSemesterId = session('admin_selected_semester_id');

                if (!$selectedSemesterId && $semesters->isNotEmpty()) {
                    $selectedSemesterId = $semesters->first()->semester_id;
                    session(['admin_selected_semester_id' => $selectedSemesterId]);
                }
            }

            // ค้นหา semester ที่ admin เลือก
            $selectedSemester = $semesters->firstWhere('semester_id', $selectedSemesterId);

            // ดึงข้อมูล semester ที่เลือกสำหรับผู้ใช้ (TA และ Teacher)
            $userSetting = DB::table('setting_semesters')->where('key', 'user_active_semester_id')->first();
            $userSelectedSemesterId = $userSetting ? $userSetting->value : null;

            if (!$userSelectedSemesterId && $semesters->isNotEmpty()) {
                $userSelectedSemesterId = $semesters->first()->semester_id;

                // บันทึกค่าเริ่มต้นลงในตาราง setting_semesters
                DB::table('setting_semesters')->updateOrInsert(
                    ['key' => 'user_active_semester_id'],
                    ['value' => $userSelectedSemesterId, 'updated_at' => now()]
                );
            }

            $userSelectedSemester = $semesters->firstWhere('semester_id', $userSelectedSemesterId);

            // ถ้าไม่พบ semester ที่ admin เลือก ให้แสดงข้อความแจ้งเตือน
            if (!$selectedSemester) {
                return view('layouts.admin.taUsers', [
                    'coursesWithTAs' => collect(),
                    'semesters' => $semesters,
                    'selectedSemester' => null,
                    'userSelectedSemester' => $userSelectedSemester
                ])->with('warning', 'ไม่พบข้อมูลภาคการศึกษาที่เลือก');
            }

            // ดึงข้อมูลรายวิชาที่มี TA ตาม semester ที่ admin เลือก
            $coursesWithTAs = Courses::where('semester_id', $selectedSemester->semester_id)
                ->whereHas('course_tas.courseTaClasses.requests', function ($query) {
                    $query->where('status', 'A')
                        ->whereNotNull('approved_at');
                })
                ->with([
                    'subjects',
                    'teachers',
                    'course_tas.student',
                    'course_tas.courseTaClasses.requests' => function ($query) {
                        $query->where('status', 'A')
                            ->whereNotNull('approved_at');
                    }
                ])
                ->get();

            Log::info('จำนวนรายวิชาที่มี TA: ' . $coursesWithTAs->count());

            return view('layouts.admin.taUsers', [
                'coursesWithTAs' => $coursesWithTAs,
                'semesters' => $semesters,
                'selectedSemester' => $selectedSemester,
                'userSelectedSemester' => $userSelectedSemester
            ]);
        } catch (\Exception $e) {
            Log::error('Error in taUsers: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    private function updateActiveSemester($semesterId)
    {
        try {

            DB::table('setting_semesters')->updateOrInsert(
                ['key' => 'active_semester_id'],
                ['value' => $semesterId, 'updated_at' => now()]
            );

            session(['active_semester_id' => $semesterId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating active semester: ' . $e->getMessage());
            return false;
        }
    }

    public function updateUserSemester(Request $request)
    {
        try {
            $semesterId = $request->input('semester_id');

            if (!$semesterId) {
                return back()->with('error', 'กรุณาเลือกภาคการศึกษา');
            }

            $semester = Semesters::find($semesterId);
            if (!$semester) {
                return back()->with('error', 'ไม่พบภาคการศึกษาที่เลือก');
            }

            // อัปเดตค่าใน setting_semesters
            DB::table('setting_semesters')
                ->updateOrInsert(
                    ['key' => 'user_active_semester_id'],
                    [
                        'value' => $semesterId,
                        'updated_at' => now()
                    ]
                );

            // อัปเดต session
            session(['user_active_semester_id' => $semesterId]);

            // Log debug information
            Log::info('User Active Semester Updated: ' . $semesterId);

            return back()->with('success', 'บันทึกการเลือกภาคการศึกษาสำหรับผู้ใช้งานเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Log::error('Error updating user semester: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }
    }

    public function getActiveSemester()
    {
        try {
            $activeSemesterId = session('active_semester_id');

            if (!$activeSemesterId) {
                $setting = DB::table('setting_semesters')->where('key', 'active_semester_id')->first();

                if ($setting) {
                    $activeSemesterId = $setting->value;
                    session(['active_semester_id' => $activeSemesterId]);
                } else {
                    $latestSemester = Semesters::orderBy('year', 'desc')
                        ->orderBy('semesters', 'desc')
                        ->first();

                    if ($latestSemester) {
                        $activeSemesterId = $latestSemester->semester_id;
                        $this->updateActiveSemester($activeSemesterId);
                    }
                }
            }

            return Semesters::find($activeSemesterId);
        } catch (\Exception $e) {
            Log::error('Error getting active semester: ' . $e->getMessage());
            return null;
        }
    }

    public function showTaDetails($course_id)
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

    public function downloadDocument($id)
    {
        try {
            $disbursement = Disbursements::findOrFail($id);

            if (!Storage::exists('public' . $disbursement->file_path)) {
                return back()->with('error', 'ไม่พบไฟล์เอกสาร');
            }

            return response()->download(storage_path('app/public' . $disbursement->file_path));
        } catch (\Exception $e) {
            Log::error('Document download error: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดาวน์โหลดเอกสาร');
        }
    }


    private function getCompensationRate($teachingType, $classType, $degreeLevel = 'undergraduate')
    {
        $rate = CompensationRate::where('teaching_type', $teachingType)
            ->where('class_type', $classType)
            ->where('degree_level', $degreeLevel)
            ->where('status', 'active')
            ->where('is_fixed_payment', false)
            ->first();

        if (!$rate) {
            // ใช้ค่าเริ่มต้นถ้าไม่พบอัตราในฐานข้อมูล
            if ($degreeLevel === 'undergraduate') {
                if ($teachingType === 'regular') {
                    return 40; // ผู้ช่วยสอน ป.ตรี ที่สอน ภาคปกติ
                } else {
                    return 50; // ผู้ช่วยสอน ป.ตรี ที่สอน ภาคพิเศษ
                }
            } else { // graduate
                if ($teachingType === 'regular') {
                    return 50; // ผู้ช่วยสอน บัณฑิต ที่สอน ภาคปกติ
                } else {
                    return 60; // ผู้ช่วยสอน บัณฑิต ที่สอน ภาคพิเศษ (กรณีไม่ใช้เหมาจ่าย)
                }
            }
        }

        return $rate->rate_per_hour;
    }

    public function taDetail($student_id)
    {
        try {
            $student = Students::find($student_id);
            $course_id = request('course_id');

            // if (!$course_id) {
            //     return back()->with('error', 'กรุณาระบุรหัสรายวิชา');
            // }

            $ta = CourseTas::with(['student', 'course.semesters'])
                ->where('student_id', $student_id)
                ->where('course_id', $course_id)
                ->firstOrFail();

            $course = $ta->course;
            $student = $ta->student;
            $semester = $ta->course->semesters;

            $start = \Carbon\Carbon::parse($semester->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($semester->end_date)->endOfDay();

            if (!\Carbon\Carbon::now()->between($start, $end)) {
                return back()->with('error', 'ไม่อยู่ในช่วงภาคการศึกษาปัจจุบัน');
            }

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
            $attendanceType = request('type', 'all');

            $allAttendances = collect();
            $regularLectureHours = 0;
            $regularLabHours = 0;
            $specialLectureHours = 0;
            $specialLabHours = 0;

            if ($attendanceType === 'all' || $attendanceType === 'N' || $attendanceType === 'S') {
                // ดึงข้อมูลการสอนปกติที่ได้รับการอนุมัติแล้ว
                $teachings = Teaching::with([
                    'attendance' => function ($query) use ($student) {
                        $query->where('student_id', $student->id)
                            ->where('approve_status', 'a');
                    },
                    'teacher',
                    'class.course.subjects',
                    'class.major'
                ])
                    ->where('class_id', 'LIKE', $ta->course_id . '%')
                    ->whereYear('start_time', $selectedDate->year)
                    ->whereMonth('start_time', $selectedDate->month)
                    ->whereHas('attendance', function ($query) use ($student) {
                        $query->where('student_id', $student->id)
                            ->where('approve_status', 'a');
                    })
                    ->when($attendanceType !== 'all', function ($query) use ($attendanceType) {
                        $query->whereHas('class.major', function ($q) use ($attendanceType) {
                            $q->where('major_type', $attendanceType);
                        });
                    })
                    ->get()
                    ->groupBy(function ($teaching) {
                        return $teaching->class->section_num;
                    });

                // ประมวลผลข้อมูลการสอนปกติ
                foreach ($teachings as $section => $sectionTeachings) {
                    foreach ($sectionTeachings as $teaching) {
                        $startTime = \Carbon\Carbon::parse($teaching->start_time);
                        $endTime = \Carbon\Carbon::parse($teaching->end_time);
                        $hours = $endTime->diffInMinutes($startTime) / 60;

                        $majorType = $teaching->class->major->major_type ?? 'N';
                        $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                        if ($majorType === 'N') {
                            if ($classType === 'LECTURE') {
                                $regularLectureHours += $hours;
                            } else {
                                $regularLabHours += $hours;
                            }
                        } else { // S
                            if ($classType === 'LECTURE') {
                                $specialLectureHours += $hours;
                            } else {
                                $specialLabHours += $hours;
                            }
                        }

                        $allAttendances->push([
                            'type' => 'regular',
                            'section' => $section,
                            'date' => $teaching->start_time,
                            'data' => $teaching,
                            'hours' => $hours,
                            'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                            'class_type' => $classType
                        ]);
                    }
                }

                // ดึงข้อมูลการสอนชดเชย
                $extraTeachingIds = Attendances::where('student_id', $student->id)
                    ->where('is_extra', true)
                    ->where('approve_status', 'a')
                    ->whereNotNull('extra_teaching_id')
                    ->pluck('extra_teaching_id')
                    ->toArray();

                $startDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth)->startOfMonth();
                $endDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth)->endOfMonth();

                $extraTeachings = ExtraTeaching::with([
                    'class.major',
                    'attendance' => function ($query) use ($student) {
                        $query->where('student_id', $student->id)
                            ->where('approve_status', 'a');
                    }
                ])
                    ->whereIn('extra_class_id', $extraTeachingIds)
                    ->whereBetween('class_date', [$startDate, $endDate])
                    ->whereHas('attendance', function ($query) use ($student) {
                        $query->where('student_id', $student->id)
                            ->where('approve_status', 'a');
                    })
                    ->get();

                // คำนวณชั่วโมงการสอนชดเชย
                foreach ($extraTeachings as $extraTeaching) {
                    $startTime = \Carbon\Carbon::parse($extraTeaching->start_time);
                    $endTime = \Carbon\Carbon::parse($extraTeaching->end_time);
                    $hours = $endTime->diffInMinutes($startTime) / 60;

                    $majorType = $extraTeaching->class->major->major_type ?? 'N';
                    $classType = $extraTeaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                    if ($majorType === 'N') {
                        // ถ้าเป็นโครงการปกติ
                        if ($classType === 'LECTURE') {
                            $regularLectureHours += $hours;
                        } else {
                            $regularLabHours += $hours;
                        }
                    } else {
                        // ถ้าเป็นโครงการพิเศษ
                        if ($classType === 'LECTURE') {
                            $specialLectureHours += $hours;
                        } else {
                            $specialLabHours += $hours;
                        }
                    }

                    // เพิ่มข้อมูลการสอนชดเชยใน allAttendances
                    $allAttendances->push([
                        'type' => 'extra',
                        'section' => $extraTeaching->class->section_num ?? 'N/A',
                        'date' => $extraTeaching->class_date . ' ' . $extraTeaching->start_time,
                        'data' => $extraTeaching,
                        'hours' => $hours,
                        'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                        'class_type' => $classType
                    ]);
                }
            }
            $extraAttendances = ExtraAttendances::with(['classes.course.subjects', 'classes.major'])
                ->where('student_id', $student->id)
                ->where('approve_status', 'A') // ตรวจสอบว่าใช้ 'A' หรือ 'a'
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->get();

            // ประมวลผลการสอนพิเศษ
            foreach ($extraAttendances as $extraAttendance) {
                $hours = $extraAttendance->duration / 60;
                $majorType = $extraAttendance->classes && $extraAttendance->classes->major
                    ? $extraAttendance->classes->major->major_type
                    : 'N';
                $classType = $extraAttendance->class_type === 'L' ? 'LAB' : 'LECTURE';

                // คำนวณชั่วโมงตามประเภท
                if ($majorType === 'N') {
                    if ($classType === 'LECTURE') {
                        $regularLectureHours += $hours;
                    } else {
                        $regularLabHours += $hours;
                    }
                } else {
                    if ($classType === 'LECTURE') {
                        $specialLectureHours += $hours;
                    } else {
                        $specialLabHours += $hours;
                    }
                }

                // เพิ่มข้อมูลในคอลเลกชัน allAttendances
                $allAttendances->push([
                    'type' => 'special', // หรือใช้ชื่อที่ชัดเจนกว่า เช่น 'extra_attendance'
                    'section' => $extraAttendance->classes ? $extraAttendance->classes->section_num : 'N/A',
                    'date' => $extraAttendance->start_work,
                    'data' => $extraAttendance,
                    'hours' => $hours,
                    'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }
            // บันทึก log สรุปชั่วโมงทั้งหมด
            Log::debug('Hour summary:', [
                'regularLecture' => $regularLectureHours,
                'regularLab' => $regularLabHours,
                'specialLecture' => $specialLectureHours,
                'specialLab' => $specialLabHours,
                'total' => $regularLectureHours + $regularLabHours + $specialLectureHours + $specialLabHours
            ]);

            $regularLectureRate = $this->getCompensationRate('regular', 'LECTURE');
            $regularLabRate = $this->getCompensationRate('regular', 'LAB');
            $specialLectureRate = $this->getCompensationRate('special', 'LECTURE');
            $specialLabRate = $this->getCompensationRate('special', 'LAB');

            // คำนวณค่าตอบแทน
            $regularLecturePay = $regularLectureHours * $regularLectureRate;
            $regularLabPay = $regularLabHours * $regularLabRate;
            $specialLecturePay = $specialLectureHours * $specialLectureRate;
            $specialLabPay = $specialLabHours * $specialLabRate;

            $regularPay = $regularLecturePay + $regularLabPay;
            $specialPay = $specialLecturePay + $specialLabPay;
            $totalPay = $regularPay + $specialPay;

            $compensation = [
                'regularLectureHours' => $regularLectureHours,
                'regularLabHours' => $regularLabHours,
                'specialLectureHours' => $specialLectureHours,
                'specialLabHours' => $specialLabHours,
                'regularHours' => $regularLectureHours + $regularLabHours,
                'specialHours' => $specialLectureHours + $specialLabHours,
                'regularLecturePay' => $regularLecturePay,
                'regularLabPay' => $regularLabPay,
                'specialLecturePay' => $specialLecturePay,
                'specialLabPay' => $specialLabPay,
                'regularPay' => $regularPay,
                'specialPay' => $specialPay,
                'totalPay' => $totalPay,

                'rates' => [
                    'regularLecture' => $regularLectureRate,
                    'regularLab' => $regularLabRate,
                    'specialLecture' => $specialLectureRate,
                    'specialLab' => $specialLabRate
                ]
            ];

            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            // คำนวณจำนวนนักศึกษาทั้งหมดในรายวิชา
            $course = Courses::with('classes')->find($ta->course_id);
            $totalStudents = $course ? $course->classes->sum('enrolled_num') : 0;

            // คำนวณงบประมาณรายวิชา
            $totalBudget = $totalStudents * 300; // 300 บาทต่อคน

            // ดึงข้อมูลการเบิกจ่ายทั้งหมดของรายวิชา (ทุก TA)
            $totalUsed = 0;
            try {
                $totalUsed = CompensationTransaction::where('course_id', $ta->course_id)
                    ->sum('actual_amount');
            } catch (\Exception $e) {
                // กรณีไม่มีตาราง CompensationTransaction ยังไม่ต้องทำอะไร
                Log::info('CompensationTransaction table might not exist yet: ' . $e->getMessage());
            }

            // คำนวณงบประมาณคงเหลือของรายวิชา
            $remainingBudget = $totalBudget - $totalUsed;

            // ตรวจสอบว่าค่าตอบแทนของเดือนนี้เกินงบประมาณที่เหลือหรือไม่
            $isExceeded = $totalPay > $remainingBudget;

            // สำหรับการแสดงผลข้อมูลเพิ่มเติม (อาจไม่จำเป็นต้องใช้)
            $totalTAs = CourseTas::where('course_id', $ta->course_id)->count();
            $budgetPerTA = $totalTAs > 0 ? $totalBudget / $totalTAs : 0;

            // ดึงข้อมูลการเบิกจ่ายเฉพาะของ TA คนนี้ (เพื่อแสดงประวัติการเบิกจ่าย)
            $totalUsedByTA = 0;
            try {
                $totalUsedByTA = CompensationTransaction::where('student_id', $student_id)
                    ->where('course_id', $ta->course_id)
                    ->sum('actual_amount');
            } catch (\Exception $e) {
                Log::info('Error getting TA transactions: ' . $e->getMessage());
            }

            $degreeLevel = $student->degree_level ?? 'undergraduate';
            $isFixedPayment = false;
            $fixedAmount = null;

            $degreeLevel = $student->degree_level ?? 'undergraduate';
            $isGraduate = in_array($degreeLevel, ['master', 'doctoral', 'graduate']);
            // ในเมธอด taDetail หรือ getMonthlyCompensationData
            if ($isGraduate && ($compensation['specialLectureHours'] > 0 || $compensation['specialLabHours'] > 0)) {
                // ดึงอัตราเหมาจ่ายจากฐานข้อมูล
                try {
                    $fixedRate = CompensationRate::where('teaching_type', 'special')
                        ->where('degree_level', 'graduate')
                        ->where('is_fixed_payment', true)
                        ->where('status', 'active')
                        ->first();

                    Log::debug("Found fixed rate record: ", ['record' => $fixedRate ? $fixedRate->toArray() : 'none']);

                    if ($fixedRate && $fixedRate->fixed_amount > 0) {
                        $isFixedPayment = true;
                        $fixedAmount = $fixedRate->fixed_amount;
                        // ปรับค่าตอบแทนให้เป็นแบบเหมาจ่าย
                        $compensation['specialPay'] = $fixedAmount;
                        $totalPay = $compensation['regularPay'] + $fixedAmount;
                        $compensation['totalPay'] = $totalPay;

                        Log::debug("Using fixed payment: {$fixedAmount}");
                    } else {
                        Log::debug("No valid fixed amount found in database");
                        $isFixedPayment = true;
                        $fixedAmount = 4000; // ค่าเริ่มต้น 4,000 บาท
                        $compensation['specialPay'] = $fixedAmount;
                        $totalPay = $compensation['regularPay'] + $fixedAmount;
                        $compensation['totalPay'] = $totalPay;

                        Log::debug("Using default fixed payment: {$fixedAmount}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error getting fixed rate: " . $e->getMessage());
                    $isFixedPayment = false;
                    $fixedAmount = 0;
                }
            }
            // เรียงข้อมูลตามวันที่
            $allAttendances = $allAttendances->sortBy('date');

            // แล้วจึงจัดกลุ่มตาม section
            $attendancesBySection = $allAttendances->groupBy('section');
            // เพิ่มบรรทัดนี้เพื่อกำหนดค่า courseTa
            $courseTa = $ta;

            // dd($allAttendances);
            // ส่งข้อมูลทั้งหมดไปยัง view
            return view('layouts.admin.detailsById', compact(
                'student',
                'course',
                // 'courseTa',
                'semester',
                'attendancesBySection',
                'monthsInSemester',
                'selectedYearMonth',
                'attendanceType',
                'compensation',
                'totalStudents',
                'totalBudget',
                'totalTAs',
                'totalUsedByTA',
                'remainingBudget',
                'isExceeded',
                'isFixedPayment',
                'fixedAmount'
            ));
        } catch (\Exception $e) {
            Log::error('Exception in taDetail: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }


    private function getAttendanceData($student_id, $selectedYearMonth, $attendanceType)
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth)->startOfMonth();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth)->endOfMonth();

        $allAttendances = collect();
        $regularHours = 0;
        $specialHours = 0;

        $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects'])
            ->whereHas('attendance', function ($query) use ($student_id) {
                $query->where('student_id', $student_id)
                    ->where('approve_status', 'A');
            })
            ->whereBetween('start_time', [$start, $end])
            ->when($attendanceType !== 'all', function ($query) use ($attendanceType) {
                $query->whereHas('class.major', function ($q) use ($attendanceType) {
                    $q->where('major_type', $attendanceType);
                });
            })
            ->get();

        foreach ($teachings as $teaching) {
            $hours = $teaching->duration / 60;
            $regularHours += $hours;

            $allAttendances->push([
                'type' => 'regular',
                'section' => $teaching->class->section_num,
                'date' => $teaching->start_time,
                'data' => $teaching,
                'hours' => $hours
            ]);
        }

        $extraAttendances = ExtraAttendances::with(['classes.course.subjects'])
            ->where('student_id', $student_id)
            ->where('approve_status', 'A')
            ->whereBetween('start_work', [$start, $end])
            ->when($attendanceType !== 'all', function ($query) use ($attendanceType) {
                $query->whereHas('classes.major', function ($q) use ($attendanceType) {
                    $q->where('major_type', $attendanceType);
                });
            })
            ->get();

        foreach ($extraAttendances as $extra) {
            $hours = $extra->duration / 60;
            $specialHours += $hours;

            $allAttendances->push([
                'type' => 'special',
                'section' => $extra->classes->section_num,
                'date' => $extra->start_work,
                'data' => $extra,
                'hours' => $hours
            ]);
        }

        return [
            'allAttendances' => $allAttendances,
            'regularHours' => $regularHours,
            'specialHours' => $specialHours
        ];


    }



    public function exportTaDetailPDF($id)
    {
        try {
            $selectedYearMonth = request('month');

            $ta = CourseTas::with(['student', 'course.semesters', 'course.teachers'])
                ->whereHas('student', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->first();

            if (!$ta) {
                return back()->with('error', 'ไม่พบข้อมูลผู้ช่วยสอน');
            }

            $student = $ta->student;
            $semester = $ta->course->semesters;

            $teacherName = $ta->course->teachers->name ?? 'อาจารย์ผู้สอน';
            $teacherPosition = $ta->course->teachers->position ?? '';
            $teacherDegree = $ta->course->teachers->degree ?? '';
            $teacherFullTitle = trim($teacherPosition . ' ' . $teacherDegree . '.' . ' ' . $teacherName);

            $headName = 'ผศ. ดร.คำรณ สุนัติ';

            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            $currentDate = \Carbon\Carbon::now();
            $thaiMonth = $currentDate->locale('th')->monthName;
            $thaiYear = $currentDate->year + 543;
            $formattedDate = [
                'day' => $currentDate->day,
                'month' => $thaiMonth,
                'year' => $thaiYear
            ];

            $monthText = $selectedDate->locale('th')->monthName . ' ' . ($selectedDate->year + 543);
            $year = $selectedDate->year + 543;

            $allAttendances = collect();
            $regularAttendances = collect();
            $specialAttendances = collect();

            $hasRegularProject = false;
            $hasSpecialProject = false;

            // Initialize variables to prevent undefined errors
            $isFixedPayment = false;
            $fixedAmount = 0;
            $specialPay = 0;
            $regularLectureHoursSum = 0;
            $regularLabHoursSum = 0;
            $specialLectureHoursSum = 0;
            $specialLabHoursSum = 0;

            // ดึงข้อมูลการสอนปกติ
            $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects', 'class.major'])
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereYear('start_time', $selectedDate->year)
                ->whereMonth('start_time', $selectedDate->month)
                ->whereHas('attendance', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('approve_status', 'A');
                })
                ->get()
                ->groupBy(function ($teaching) {
                    return $teaching->class->section_num;
                });

            foreach ($teachings as $section => $sectionTeachings) {
                foreach ($sectionTeachings as $teaching) {
                    $startTime = \Carbon\Carbon::parse($teaching->start_time);
                    $endTime = \Carbon\Carbon::parse($teaching->end_time);
                    $hours = $endTime->diffInMinutes($startTime) / 60;

                    $majorType = $teaching->class->major->major_type ?? 'N';
                    $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                    if ($majorType === 'N') {
                        $hasRegularProject = true;

                        if ($classType === 'LECTURE') {
                            $regularLectureHoursSum += $hours;
                        } else {
                            $regularLabHoursSum += $hours;
                        }

                        $regularAttendances->push([
                            'type' => 'regular',
                            'data' => $teaching,
                            'hours' => $hours,
                            'class_type' => $classType
                        ]);
                    } else {
                        $hasSpecialProject = true;

                        if ($classType === 'LECTURE') {
                            $specialLectureHoursSum += $hours;
                        } else {
                            $specialLabHoursSum += $hours;
                        }

                        $specialAttendances->push([
                            'type' => 'regular',
                            'data' => $teaching,
                            'hours' => $hours,
                            'class_type' => $classType
                        ]);
                    }

                    $allAttendances->push([
                        'type' => 'regular',
                        'section' => $section,
                        'date' => $teaching->start_time,
                        'data' => $teaching,
                        'hours' => $hours,
                        'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                        'class_type' => $classType
                    ]);
                }
            }

            // ดึงข้อมูลการสอนชดเชย
            $extraTeachingIds = Attendances::where('student_id', $student->id)
                ->where('is_extra', true)
                ->where('approve_status', 'A')
                ->whereNotNull('extra_teaching_id')
                ->pluck('extra_teaching_id')
                ->toArray();

            $extraTeachings = ExtraTeaching::with(['class.major'])
                ->whereIn('extra_class_id', $extraTeachingIds)
                ->whereBetween('class_date', [
                    $selectedDate->startOfMonth()->format('Y-m-d'),
                    $selectedDate->endOfMonth()->format('Y-m-d')
                ])
                ->get();

            foreach ($extraTeachings as $teaching) {
                $startTime = \Carbon\Carbon::parse($teaching->start_time);
                $endTime = \Carbon\Carbon::parse($teaching->end_time);
                $hours = $endTime->diffInMinutes($startTime) / 60;

                $majorType = $teaching->class->major->major_type ?? 'N';
                $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                if ($majorType === 'N') {
                    $hasRegularProject = true;

                    if ($classType === 'LECTURE') {
                        $regularLectureHoursSum += $hours;
                    } else {
                        $regularLabHoursSum += $hours;
                    }

                    $regularAttendances->push([
                        'type' => 'extra',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                } else {
                    $hasSpecialProject = true;

                    if ($classType === 'LECTURE') {
                        $specialLectureHoursSum += $hours;
                    } else {
                        $specialLabHoursSum += $hours;
                    }

                    $specialAttendances->push([
                        'type' => 'extra',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                }

                $allAttendances->push([
                    'type' => 'extra',
                    'section' => $teaching->class->section_num ?? 'N/A',
                    'date' => $teaching->class_date . ' ' . $teaching->start_time,
                    'data' => $teaching,
                    'hours' => $hours,
                    'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }

            // ดึงข้อมูลการทำงานพิเศษ
            $extraAttendances = ExtraAttendances::with(['classes.course.subjects', 'classes.major'])
                ->where('student_id', $student->id)
                ->where('approve_status', 'A')
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->get();

            foreach ($extraAttendances as $attendance) {
                $hours = $attendance->duration / 60;
                $majorType = $attendance->classes && $attendance->classes->major
                    ? $attendance->classes->major->major_type
                    : 'N';
                $classType = $attendance->class_type === 'L' ? 'LAB' : 'LECTURE';

                if ($majorType === 'N') {
                    $hasRegularProject = true;

                    if ($classType === 'LECTURE') {
                        $regularLectureHoursSum += $hours;
                    } else {
                        $regularLabHoursSum += $hours;
                    }

                    $regularAttendances->push([
                        'type' => 'special',
                        'data' => $attendance,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                } else {
                    $hasSpecialProject = true;

                    if ($classType === 'LECTURE') {
                        $specialLectureHoursSum += $hours;
                    } else {
                        $specialLabHoursSum += $hours;
                    }

                    $specialAttendances->push([
                        'type' => 'special',
                        'data' => $attendance,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                }

                $allAttendances->push([
                    'type' => 'special',
                    'section' => $attendance->classes ? $attendance->classes->section_num : 'N/A',
                    'date' => $attendance->start_work,
                    'data' => $attendance,
                    'hours' => $hours,
                    'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }

            // ดึงข้อมูลรายรับรายจ่ายหรือการเบิกจ่ายที่บันทึกไว้
            $transaction = CompensationTransaction::where('student_id', $student->id)
                ->where('course_id', $ta->course_id)
                ->where('month_year', $selectedYearMonth)
                ->first();

            // ดึงงบประมาณรายวิชา
            $courseBudget = CourseBudget::where('course_id', $ta->course_id)->first();

            // ดึงอัตราค่าตอบแทน
            $regularRate = $this->getCompensationRate('regular', 'LECTURE', $student->degree_level ?? 'undergraduate');
            $specialRate = $this->getCompensationRate('special', 'LECTURE', $student->degree_level ?? 'undergraduate');

            // คำนวณค่าตอบแทน
            $regularPay = ($regularLectureHoursSum + $regularLabHoursSum) * $regularRate;
            $specialPay = ($specialLectureHoursSum + $specialLabHoursSum) * $specialRate;

            // ตรวจสอบว่าเป็นผู้ช่วยสอนระดับบัณฑิตศึกษาและสอนในโครงการพิเศษหรือไม่
            $isGraduate = in_array($student->degree_level, ['master', 'doctoral', 'graduate']);
            $isFixedPayment = $isGraduate && $hasSpecialProject;

            if ($isFixedPayment) {
                // ดึงอัตราเหมาจ่ายจากฐานข้อมูล
                $fixedAmount = $this->getFixedCompensationRate('special', $student->degree_level ?? 'graduate');
                $specialPay = $fixedAmount ?? 4000; // ค่าเริ่มต้น 4,000 บาท
            }

            $totalPay = $regularPay + $specialPay;

            // ตรวจสอบว่ามีการบันทึกรายการเบิกจ่ายไว้หรือไม่
            if ($transaction) {
                $totalPay = $transaction->actual_amount;
            } else if ($courseBudget) {
                // ตรวจสอบงบประมาณคงเหลือ
                $remainingBudget = $courseBudget->remaining_budget;
                if ($totalPay > $remainingBudget) {
                    $totalPay = $remainingBudget; // จำกัดวงเงินไม่เกินงบประมาณที่เหลือ
                }
            }

            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            $compensationRates = [
                'regularLecture' => $regularRate,
                'specialLecture' => $specialRate
            ];

            $pdf = PDF::loadView('exports.detailPDF', compact(
                'student',
                'semester',
                'attendancesBySection',
                'selectedYearMonth',
                'monthText',
                'year',
                'compensationRates',
                'headName',
                'formattedDate',
                'teacherFullTitle',
                'hasRegularProject',
                'hasSpecialProject',
                'isFixedPayment',
                'fixedAmount',
                'specialPay',
                'regularAttendances',
                'specialAttendances',
                'regularLectureHoursSum',
                'regularLabHoursSum',
                'specialLectureHoursSum',
                'specialLabHoursSum',
                'totalPay',
                'transaction'
            ));

            $pdf->setPaper('A4');
            $fileName = 'TA-Compensation-Detail-' . $student->student_id . '-' . $selectedYearMonth . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้าง PDF: ' . $e->getMessage());
        }
    }

    private function convertNumberToThaiBaht($number)
    {
        $numberText = number_format($number, 2, '.', '');
        $textBaht = \App\Helpers\ThaiNumberHelper::convertToText($numberText);
        return $textBaht . 'ถ้วน';
    }

    public function exportResultPDF($id)
    {
        try {
            $selectedYearMonth = request('month');

            $ta = CourseTas::with(['student', 'course.semesters', 'course.teachers'])
                ->whereHas('student', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->first();

            if (!$ta) {
                return back()->with('error', 'ไม่พบข้อมูลผู้ช่วยสอน');
            }

            $student = $ta->student;
            $semester = $ta->course->semesters;

            $teacherName = $ta->course->teachers->name ?? 'อาจารย์ผู้สอน';
            $teacherPosition = $ta->course->teachers->position ?? '';
            $teacherDegree = $ta->course->teachers->degree ?? '';
            $teacherFullTitle = trim($teacherPosition . ' ' . $teacherDegree . '.' . ' ' . $teacherName);

            $headName = 'ผศ. ดร.คำรณ สุนัติ';

            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            $currentDate = \Carbon\Carbon::now();
            $thaiMonth = $currentDate->locale('th')->monthName;
            $thaiYear = $currentDate->year + 543;
            $formattedDate = [
                'day' => $currentDate->day,
                'month' => $thaiMonth,
                'year' => $thaiYear
            ];

            $monthText = $selectedDate->locale('th')->monthName . ' ' . ($selectedDate->year + 543);
            $year = $selectedDate->year + 543;

            // Initialize collections and variables
            $allAttendances = collect();
            $regularAttendances = collect();
            $specialAttendances = collect();

            $hasRegularProject = false;
            $hasSpecialProject = false;

            $regularLectureHours = 0;
            $regularLabHours = 0;
            $specialLectureHours = 0;
            $specialLabHours = 0;

            // ดึงข้อมูลการสอนปกติ
            $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects', 'class.major'])
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereYear('start_time', $selectedDate->year)
                ->whereMonth('start_time', $selectedDate->month)
                ->whereHas('attendance', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('approve_status', 'A');
                })
                ->get()
                ->groupBy(function ($teaching) {
                    return $teaching->class->section_num;
                });

            // ประมวลผลข้อมูลการสอนปกติ
            foreach ($teachings as $section => $sectionTeachings) {
                foreach ($sectionTeachings as $teaching) {
                    $startTime = \Carbon\Carbon::parse($teaching->start_time);
                    $endTime = \Carbon\Carbon::parse($teaching->end_time);
                    $hours = $endTime->diffInMinutes($startTime) / 60;

                    $majorType = $teaching->class->major->major_type ?? 'N';
                    $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                    if ($majorType === 'N' || $majorType === '') {
                        $hasRegularProject = true;

                        if ($classType === 'LECTURE') {
                            $regularLectureHours += $hours;
                        } else {
                            $regularLabHours += $hours;
                        }

                        $regularAttendances->push([
                            'type' => 'regular',
                            'data' => $teaching,
                            'hours' => $hours,
                            'class_type' => $classType
                        ]);
                    } else {
                        $hasSpecialProject = true;

                        if ($classType === 'LECTURE') {
                            $specialLectureHours += $hours;
                        } else {
                            $specialLabHours += $hours;
                        }

                        $specialAttendances->push([
                            'type' => 'regular',
                            'data' => $teaching,
                            'hours' => $hours,
                            'class_type' => $classType
                        ]);
                    }

                    $allAttendances->push([
                        'type' => 'regular',
                        'section' => $section,
                        'date' => $teaching->start_time,
                        'data' => $teaching,
                        'hours' => $hours,
                        'teaching_type' => ($majorType === 'N' || $majorType === '') ? 'regular' : 'special',
                        'class_type' => $classType
                    ]);
                }
            }

            // ดึงข้อมูลการสอนชดเชย
            $extraTeachingIds = Attendances::where('student_id', $student->id)
                ->where('is_extra', true)
                ->where('approve_status', 'A')
                ->whereNotNull('extra_teaching_id')
                ->pluck('extra_teaching_id')
                ->toArray();

            $extraTeachings = ExtraTeaching::with(['class.major', 'class.course.subjects'])
                ->whereIn('extra_class_id', $extraTeachingIds)
                ->whereBetween('class_date', [
                    $selectedDate->startOfMonth()->format('Y-m-d'),
                    $selectedDate->endOfMonth()->format('Y-m-d')
                ])
                ->get();

            // ประมวลผลข้อมูลการสอนชดเชย
            foreach ($extraTeachings as $teaching) {
                $startTime = \Carbon\Carbon::parse($teaching->start_time);
                $endTime = \Carbon\Carbon::parse($teaching->end_time);
                $hours = $endTime->diffInMinutes($startTime) / 60;

                $majorType = $teaching->class->major->major_type ?? 'N';
                $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                if ($majorType === 'N' || $majorType === '') {
                    $hasRegularProject = true;

                    if ($classType === 'LECTURE') {
                        $regularLectureHours += $hours;
                    } else {
                        $regularLabHours += $hours;
                    }

                    $regularAttendances->push([
                        'type' => 'extra',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                } else {
                    $hasSpecialProject = true;

                    if ($classType === 'LECTURE') {
                        $specialLectureHours += $hours;
                    } else {
                        $specialLabHours += $hours;
                    }

                    $specialAttendances->push([
                        'type' => 'extra',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                }

                $allAttendances->push([
                    'type' => 'extra',
                    'section' => $teaching->class->section_num ?? 'N/A',
                    'date' => $teaching->class_date . ' ' . $teaching->start_time,
                    'data' => $teaching,
                    'hours' => $hours,
                    'teaching_type' => ($majorType === 'N' || $majorType === '') ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }

            // ดึงข้อมูลการทำงานพิเศษ
            $extraAttendances = ExtraAttendances::with(['classes.course.subjects', 'classes.major'])
                ->where('student_id', $student->id)
                ->where('approve_status', 'A')
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->get();

            // ประมวลผลข้อมูลการทำงานพิเศษ
            foreach ($extraAttendances as $attendance) {
                $hours = $attendance->duration / 60;

                // ตรวจสอบและป้องกันกรณีข้อมูลไม่ครบถ้วน
                if (!$attendance->classes || !$attendance->classes->major) {
                    // กรณีไม่มีข้อมูล major เราถือว่าเป็นโครงการปกติ (N)
                    $majorType = 'N';
                } else {
                    $majorType = $attendance->classes->major->major_type ?? 'N';
                }

                $classType = $attendance->class_type === 'L' ? 'LAB' : 'LECTURE';

                if ($majorType === 'N' || $majorType === '') {
                    $hasRegularProject = true;

                    if ($classType === 'LECTURE') {
                        $regularLectureHours += $hours;
                    } else {
                        $regularLabHours += $hours;
                    }

                    $regularAttendances->push([
                        'type' => 'special',
                        'data' => $attendance,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                } else {
                    $hasSpecialProject = true;

                    if ($classType === 'LECTURE') {
                        $specialLectureHours += $hours;
                    } else {
                        $specialLabHours += $hours;
                    }

                    $specialAttendances->push([
                        'type' => 'special',
                        'data' => $attendance,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                }

                $allAttendances->push([
                    'type' => 'special',
                    'section' => $attendance->classes ? $attendance->classes->section_num : 'N/A',
                    'date' => $attendance->start_work,
                    'data' => $attendance,
                    'hours' => $hours,
                    'teaching_type' => ($majorType === 'N' || $majorType === '') ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }

            // ดึงอัตราค่าตอบแทน
            $degreeLevel = $student->degree_level ?? 'undergraduate';
            $regularLectureRate = $this->getCompensationRate('regular', 'LECTURE', $degreeLevel);
            $regularLabRate = $this->getCompensationRate('regular', 'LAB', $degreeLevel);
            $specialLectureRate = $this->getCompensationRate('special', 'LECTURE', $degreeLevel);
            $specialLabRate = $this->getCompensationRate('special', 'LAB', $degreeLevel);

            // คำนวณค่าตอบแทน
            $regularLecturePay = $regularLectureHours * $regularLectureRate;
            $regularLabPay = $regularLabHours * $regularLabRate;
            $specialLecturePay = $specialLectureHours * $specialLectureRate;
            $specialLabPay = $specialLabHours * $specialLabRate;

            $regularPay = $regularLecturePay + $regularLabPay;
            $specialPay = $specialLecturePay + $specialLabPay;

            // ตรวจสอบว่าเป็นผู้ช่วยสอนระดับบัณฑิตศึกษาและสอนในโครงการพิเศษหรือไม่
            $isGraduate = in_array($student->degree_level, ['master', 'doctoral', 'graduate']);
            $isFixedPayment = $isGraduate && $hasSpecialProject;
            $fixedAmount = null;

            if ($isFixedPayment) {
                // ดึงอัตราเหมาจ่ายจากฐานข้อมูล
                $fixedRate = CompensationRate::where('teaching_type', 'special')
                    ->where('degree_level', 'graduate')
                    ->where('is_fixed_payment', true)
                    ->where('status', 'active')
                    ->first();

                if ($fixedRate && $fixedRate->fixed_amount > 0) {
                    $fixedAmount = $fixedRate->fixed_amount;
                } else {
                    $fixedAmount = 4000; // ค่าเริ่มต้น 4,000 บาท
                }

                $specialPay = $fixedAmount;
            }

            $totalPay = $regularPay + $specialPay;

            // ตรวจสอบว่ามีการบันทึกรายการเบิกจ่ายไว้หรือไม่
            $transaction = CompensationTransaction::where('student_id', $student->id)
                ->where('course_id', $ta->course_id)
                ->where('month_year', $selectedYearMonth)
                ->first();

            // กำหนดยอดรับจริงให้เท่ากับยอดคำนวณปกติ (ไม่มีการแบ่งส่วน)
            $actualRegularPay = $regularPay;
            $actualSpecialPay = $specialPay;
            $actualTotalPay = $totalPay;

            // ถ้ามีการบันทึกรายการในตาราง CompensationTransaction ให้ใช้ค่านั้นเป็นยอดรวมทั้งหมด
            if ($transaction) {
                // ไม่มีการแบ่งยอดระหว่างโครงการ ใช้ยอดตามที่คำนวณไว้
                $actualTotalPay = $transaction->actual_amount;

                // ตัวแปรเหล่านี้ใช้เฉพาะในการแสดงผล ไม่มีผลต่อการเบิกจ่ายจริง
                $actualRegularPay = $hasRegularProject ? $regularPay : 0;
                $actualSpecialPay = $hasSpecialProject ? $specialPay : 0;
            }

            // เรียงข้อมูลตามวันที่และจัดกลุ่มตาม section
            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            // เตรียมข้อมูลอัตราค่าตอบแทน
            $compensationRates = [
                'regularLecture' => $regularLectureRate,
                'regularLab' => $regularLabRate,
                'specialLecture' => $specialLectureRate,
                'specialLab' => $specialLabRate
            ];

            // คำนวณจำนวนเงินในแต่ละแบบการจ่าย (ตามชั่วโมงและเหมาจ่าย)
            $hourlySpecialPay = ($specialLectureHours * $specialLectureRate) + ($specialLabHours * $specialLabRate);
            $fixedSpecialPay = $isFixedPayment ? ($fixedAmount ?? 4000) : 0;

            // สร้าง PDF
            $pdf = PDF::loadView('exports.resultPDF', compact(
                'student',
                'semester',
                'attendancesBySection',
                'selectedYearMonth',
                'monthText',
                'year',
                'compensationRates',
                'headName',
                'formattedDate',
                'teacherFullTitle',
                'hasRegularProject',
                'hasSpecialProject',
                'isFixedPayment',
                'fixedAmount',
                'specialPay',
                'regularPay',
                'totalPay',
                'hourlySpecialPay',
                'fixedSpecialPay',
                'actualRegularPay',
                'actualSpecialPay',
                'actualTotalPay',
                'transaction',
                'regularLectureHours',
                'regularLabHours',
                'specialLectureHours',
                'specialLabHours'
            ));

            $pdf->setPaper('A4', 'landscape');
            $fileName = 'TA-Result-' . $student->student_id . '-' . $selectedYearMonth . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // เพิ่ม stack trace เพื่อช่วยในการแก้ไขข้อผิดพลาด
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้าง PDF: ' . $e->getMessage());
        }
    }


    public function index()
    {
        $announces = Announce::with('semester')
            ->latest()
            ->paginate(5);

        return view('layouts.admin.index', compact('announces'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $semesters = Semesters::orderBy('year', 'desc')
            ->orderBy('semesters', 'desc')
            ->get();
        return view('layouts.admin.create', compact('semesters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'semester_id' => 'required|exists:semesters,semester_id',
        ]);

        Announce::create([
            'title' => $request->title,
            'description' => $request->description,
            'semester_id' => $request->semester_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()
            ->route('announces.index')
            ->with('success', 'ประกาศถูกสร้างเรียบร้อย');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announce $announce)
    {
        return view('layouts.admin.show', compact('announce'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announce $announce)
    {
        $semesters = Semesters::orderBy('year', 'desc')
            ->orderBy('semesters', 'desc')
            ->get();

        return view('layouts.admin.edit', compact('announce', 'semesters'));
    }

    public function update(Request $request, Announce $announce)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'semester_id' => 'required|exists:semesters,semester_id',
        ]);

        $announce->update([
            'title' => $request->title,
            'description' => $request->description,
            'semester_id' => $request->semester_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()
            ->route('announces.index')
            ->with('success', 'ประกาศถูกอัปเดตเรียบร้อย');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announce $announce)
    {
        $announce->delete();
        return redirect()
            ->route('announces.index')
            ->with('success', 'announce deleted successfully');
    }

    public function taRequests()
    {
        try {
            $requests = TeacherRequest::with([
                'teacher:teacher_id,name,email',
                'course.subjects',
                'details.students.courseTa.student'
            ])
                ->latest()
                ->get();

            return view('layouts.admin.ta-requests.index', compact('requests'));
        } catch (\Exception $e) {
            Log::error('Error fetching TA requests: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล');
        }
    }

    public function processTARequest(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:A,R',
            'comment' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $taRequest = TeacherRequest::with(['details.students.courseTa'])
                ->findOrFail($id);

            if ($taRequest->status !== 'W') {
                return back()->with('error', 'คำร้องนี้ได้รับการดำเนินการแล้ว');
            }

            $taRequest->update([
                'status' => $validated['status'],
                'admin_processed_at' => now(),
                'admin_user_id' => Auth::id(),
                'admin_comment' => $validated['comment'] ?? null
            ]);

            foreach ($taRequest->details as $detail) {
                foreach ($detail->students as $student) {
                    // หา courseTaClasses ที่มีอยู่แล้ว
                    $courseTaClasses = CourseTaClasses::where('course_ta_id', $student->course_ta_id)->get();

                    foreach ($courseTaClasses as $courseTaClass) {
                        Requests::updateOrCreate(
                            [
                                'course_ta_class_id' => $courseTaClass->id
                            ],
                            [
                                'status' => $validated['status'],
                                'comment' => $validated['comment'] ?? null,
                                'approved_at' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();
            $statusText = $validated['status'] === 'A' ? 'อนุมัติ' : 'ปฏิเสธ';
            return back()->with('success', "คำร้องได้รับการ{$statusText}เรียบร้อยแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing TA request: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดำเนินการ: ' . $e->getMessage());
        }
    }


    /**
     * ดึงและคำนวณข้อมูลการสอนของผู้ช่วยสอนแยกตามประเภท
     * 
     * @param int $studentId รหัสผู้ช่วยสอน
     * @param string $courseId รหัสรายวิชา
     * @param string $yearMonth ปี-เดือน (Y-m)
     * @return array ข้อมูลการสอนและชั่วโมงแยกตามประเภท
     */
    private function getAttendancesData($studentId, $courseId, $yearMonth)
    {
        $student = Students::findOrFail($studentId);
        $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);

        $allAttendances = collect();
        $regularAttendances = collect();
        $specialAttendances = collect();

        $hasRegularProject = false;
        $hasSpecialProject = false;

        $regularLectureHoursSum = 0;
        $regularLabHoursSum = 0;
        $specialLectureHoursSum = 0;
        $specialLabHoursSum = 0;

        // ดึงข้อมูลการสอนปกติ
        $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects', 'class.major'])
            ->where('class_id', 'LIKE', $courseId . '%')
            ->whereYear('start_time', $selectedDate->year)
            ->whereMonth('start_time', $selectedDate->month)
            ->whereHas('attendance', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->where('approve_status', 'A');
            })
            ->get();

        // จัดกลุ่มตาม section
        $groupedTeachings = $teachings->groupBy(function ($teaching) {
            return $teaching->class->section_num ?? 'ไม่ระบุ';
        });

        foreach ($groupedTeachings as $section => $sectionTeachings) {
            foreach ($sectionTeachings as $teaching) {
                $startTime = \Carbon\Carbon::parse($teaching->start_time);
                $endTime = \Carbon\Carbon::parse($teaching->end_time);
                $hours = $endTime->diffInMinutes($startTime) / 60;

                $majorType = $teaching->class->major->major_type ?? 'N';
                $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                if ($majorType === 'N' || $majorType === '') {
                    $hasRegularProject = true;

                    if ($classType === 'LECTURE') {
                        $regularLectureHoursSum += $hours;
                    } else {
                        $regularLabHoursSum += $hours;
                    }

                    $regularAttendances->push([
                        'type' => 'regular',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                } else {
                    $hasSpecialProject = true;

                    if ($classType === 'LECTURE') {
                        $specialLectureHoursSum += $hours;
                    } else {
                        $specialLabHoursSum += $hours;
                    }

                    $specialAttendances->push([
                        'type' => 'regular',
                        'data' => $teaching,
                        'hours' => $hours,
                        'class_type' => $classType
                    ]);
                }

                $allAttendances->push([
                    'type' => 'regular',
                    'section' => $section,
                    'date' => $teaching->start_time,
                    'data' => $teaching,
                    'hours' => $hours,
                    'teaching_type' => $majorType === 'N' || $majorType === '' ? 'regular' : 'special',
                    'class_type' => $classType
                ]);
            }
        }

        // ดึงข้อมูลการสอนชดเชย (ExtraTeaching)
        $extraTeachingIds = Attendances::where('student_id', $student->id)
            ->where('is_extra', true)
            ->where('approve_status', 'A')
            ->whereNotNull('extra_teaching_id')
            ->pluck('extra_teaching_id')
            ->toArray();

        $extraTeachings = ExtraTeaching::with(['class.major', 'class.course.subjects'])
            ->whereIn('extra_class_id', $extraTeachingIds)
            ->whereBetween('class_date', [
                $selectedDate->startOfMonth()->format('Y-m-d'),
                $selectedDate->endOfMonth()->format('Y-m-d')
            ])
            ->get();

        foreach ($extraTeachings as $teaching) {
            $startTime = \Carbon\Carbon::parse($teaching->start_time);
            $endTime = \Carbon\Carbon::parse($teaching->end_time);
            $hours = $endTime->diffInMinutes($startTime) / 60;

            $majorType = $teaching->class->major->major_type ?? 'N';
            $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

            if ($majorType === 'N' || $majorType === '') {
                $hasRegularProject = true;

                if ($classType === 'LECTURE') {
                    $regularLectureHoursSum += $hours;
                } else {
                    $regularLabHoursSum += $hours;
                }

                $regularAttendances->push([
                    'type' => 'extra',
                    'data' => $teaching,
                    'hours' => $hours,
                    'class_type' => $classType
                ]);
            } else {
                $hasSpecialProject = true;

                if ($classType === 'LECTURE') {
                    $specialLectureHoursSum += $hours;
                } else {
                    $specialLabHoursSum += $hours;
                }

                $specialAttendances->push([
                    'type' => 'extra',
                    'data' => $teaching,
                    'hours' => $hours,
                    'class_type' => $classType
                ]);
            }

            $allAttendances->push([
                'type' => 'extra',
                'section' => $teaching->class->section_num ?? 'ไม่ระบุ',
                'date' => $teaching->class_date . ' ' . $teaching->start_time,
                'data' => $teaching,
                'hours' => $hours,
                'teaching_type' => $majorType === 'N' || $majorType === '' ? 'regular' : 'special',
                'class_type' => $classType
            ]);
        }

        // ดึงข้อมูลการทำงานพิเศษ (ExtraAttendances)
        $extraAttendances = ExtraAttendances::with(['classes.course.subjects', 'classes.major'])
            ->where('student_id', $student->id)
            ->where('approve_status', 'A')
            ->whereYear('start_work', $selectedDate->year)
            ->whereMonth('start_work', $selectedDate->month)
            ->get();

        foreach ($extraAttendances as $attendance) {
            $hours = $attendance->duration / 60;

            // ตรวจสอบและป้องกันกรณีข้อมูลไม่ครบถ้วน
            if (!$attendance->classes || !$attendance->classes->major) {
                // กรณีไม่มีข้อมูล major เราถือว่าเป็นโครงการปกติ (N)
                $majorType = 'N';
                $section = 'ไม่ระบุ';
            } else {
                $majorType = $attendance->classes->major->major_type ?? 'N';
                $section = $attendance->classes->section_num ?? 'ไม่ระบุ';
            }

            $classType = $attendance->class_type === 'L' ? 'LAB' : 'LECTURE';

            if ($majorType === 'N' || $majorType === '') {
                $hasRegularProject = true;

                if ($classType === 'LECTURE') {
                    $regularLectureHoursSum += $hours;
                } else {
                    $regularLabHoursSum += $hours;
                }

                $regularAttendances->push([
                    'type' => 'special',
                    'data' => $attendance,
                    'hours' => $hours,
                    'class_type' => $classType
                ]);
            } else {
                $hasSpecialProject = true;

                if ($classType === 'LECTURE') {
                    $specialLectureHoursSum += $hours;
                } else {
                    $specialLabHoursSum += $hours;
                }

                $specialAttendances->push([
                    'type' => 'special',
                    'data' => $attendance,
                    'hours' => $hours,
                    'class_type' => $classType
                ]);
            }

            $allAttendances->push([
                'type' => 'special',
                'section' => $section,
                'date' => $attendance->start_work,
                'data' => $attendance,
                'hours' => $hours,
                'teaching_type' => $majorType === 'N' || $majorType === '' ? 'regular' : 'special',
                'class_type' => $classType
            ]);
        }

        // เรียงลำดับตามวันที่และจัดกลุ่มตาม section
        $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

        return [
            'allAttendances' => $allAttendances,
            'regularAttendances' => $regularAttendances,
            'specialAttendances' => $specialAttendances,
            'attendancesBySection' => $attendancesBySection,
            'hasRegularProject' => $hasRegularProject,
            'hasSpecialProject' => $hasSpecialProject,
            'regularLectureHoursSum' => $regularLectureHoursSum,
            'regularLabHoursSum' => $regularLabHoursSum,
            'specialLectureHoursSum' => $specialLectureHoursSum,
            'specialLabHoursSum' => $specialLabHoursSum
        ];
    }


    public function exportFromTemplate($id, Request $request)
    {
        try {
            $selectedYearMonth = $request->input('month');
            if (empty($selectedYearMonth)) {
                return back()->with('error', 'กรุณาระบุเดือนที่ต้องการ export ข้อมูล');
            }

            // ดึงข้อมูล TA และรายวิชา
            $ta = CourseTas::with(['student', 'course.semesters', 'course.teachers', 'course.subjects'])
                ->whereHas('student', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->first();

            if (!$ta) {
                return back()->with('error', 'ไม่พบข้อมูลผู้ช่วยสอน');
            }

            $student = $ta->student;
            $course = $ta->course;
            $semester = $ta->course->semesters;

            // ข้อมูลอาจารย์ผู้สอน
            $teacherName = $ta->course->teachers->name ?? '';
            $teacherPosition = $ta->course->teachers->position ?? '';
            $teacherDegree = $ta->course->teachers->degree ?? '';
            $teacherFullTitle = trim($teacherPosition . ' ' . $teacherDegree . '.' . ' ' . $teacherName);

            // ข้อมูลรายวิชา
            $subjectName = $ta->course->subjects->subject_id . ' ' . $ta->course->subjects->name_en;

            // แปลงข้อมูลเดือนปี
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            // ดึงข้อมูลการเข้าสอนทั้งหมด
            $attendanceData = $this->getAttendancesData($student->id, $ta->course_id, $selectedYearMonth);

            // ดึงข้อมูลที่จำเป็นจาก attendanceData
            $regularAttendances = $attendanceData['regularAttendances'];
            $specialAttendances = $attendanceData['specialAttendances'];
            $regularLectureHours = $attendanceData['regularLectureHoursSum'];
            $regularLabHours = $attendanceData['regularLabHoursSum'];
            $specialLectureHours = $attendanceData['specialLectureHoursSum'];
            $specialLabHours = $attendanceData['specialLabHoursSum'];
            $hasRegularProject = $attendanceData['hasRegularProject'];
            $hasSpecialProject = $attendanceData['hasSpecialProject'];

            // ดึงข้อมูลรายรับรายจ่ายหรือการเบิกจ่ายที่บันทึกไว้
            $transaction = CompensationTransaction::where('student_id', $student->id)
                ->where('course_id', $ta->course_id)
                ->where('month_year', $selectedYearMonth)
                ->first();

            // ดึงงบประมาณรายวิชา
            $courseBudget = CourseBudget::where('course_id', $ta->course_id)->first();

            // ดึงอัตราค่าตอบแทน
            $degreeLevel = $student->degree_level ?? 'undergraduate';
            $regularLectureRate = $this->getCompensationRate('regular', 'LECTURE', $degreeLevel);
            $regularLabRate = $this->getCompensationRate('regular', 'LAB', $degreeLevel);
            $specialLectureRate = $this->getCompensationRate('special', 'LECTURE', $degreeLevel);
            $specialLabRate = $this->getCompensationRate('special', 'LAB', $degreeLevel);

            // คำนวณค่าตอบแทน
            $regularLecturePay = $regularLectureHours * $regularLectureRate;
            $regularLabPay = $regularLabHours * $regularLabRate;
            $specialLecturePay = $specialLectureHours * $specialLectureRate;
            $specialLabPay = $specialLabHours * $specialLabRate;

            $regularPay = $regularLecturePay + $regularLabPay;
            $specialPay = $specialLecturePay + $specialLabPay;

            // ตรวจสอบว่าเป็นการจ่ายแบบเหมาจ่ายหรือไม่
            $isGraduate = in_array($student->degree_level, ['master', 'doctoral', 'graduate']);
            $isFixedPayment = $isGraduate && ($specialLectureHours > 0 || $specialLabHours > 0);
            $fixedAmount = null;

            if ($isFixedPayment) {
                $fixedAmount = $this->getFixedCompensationRate('special', $degreeLevel);
                if ($fixedAmount) {
                    $specialPay = $fixedAmount; // ใช้อัตราเหมาจ่ายจากฐานข้อมูล
                } else {
                    $specialPay = 4000; // ค่าเริ่มต้น 4,000 บาท
                }
            }

            $totalPay = $regularPay + $specialPay;

            // ตรวจสอบว่ามีการบันทึกรายการเบิกจ่ายไว้หรือไม่
            if ($transaction) {
                // ถ้ามีการบันทึกรายการเบิกจ่ายแล้ว ใช้ข้อมูลจากรายการนั้น
                $actualAmount = $transaction->actual_amount;

                // แบ่งเงินระหว่างโครงการปกติและพิเศษตามสัดส่วน
                if ($regularPay + $specialPay > 0) {
                    $regularRatio = $regularPay / ($regularPay + $specialPay);
                    $specialRatio = $specialPay / ($regularPay + $specialPay);

                    $regularPay = $actualAmount * $regularRatio;
                    $specialPay = $actualAmount * $specialRatio;
                }

                $totalPay = $actualAmount;
            } else if ($courseBudget) {
                // ตรวจสอบงบประมาณคงเหลือ
                $remainingBudget = $courseBudget->remaining_budget;

                if ($totalPay > $remainingBudget) {
                    // ถ้างบไม่พอต้องปรับลด
                    if ($regularPay + $specialPay > 0) {
                        $ratio = $remainingBudget / $totalPay;
                        $regularPay = $regularPay * $ratio;
                        $specialPay = $specialPay * $ratio;
                        $totalPay = $remainingBudget;
                    }
                }
            }

            // --------- เริ่มสร้าง Excel ---------

            // เตรียมข้อมูลสำหรับทำ Excel
            $semesterValue = $semester->semesters ?? '';
            if ($semesterValue == 'ต้น' || $semesterValue == '1') {
                $semesterText = "ภาคการศึกษา (/) ต้น     ( ) ปลาย     ( ) ฤดูร้อน";
            } elseif ($semesterValue == 'ปลาย' || $semesterValue == '2') {
                $semesterText = "ภาคการศึกษา ( ) ต้น     (/) ปลาย     ( ) ฤดูร้อน";
            } else {
                $semesterText = "ภาคการศึกษา ( ) ต้น     ( ) ปลาย     (/) ฤดูร้อน";
            }

            $yearText = "ปีการศึกษา " . ($semester->year + 543);

            $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);
            $monthName = $date->locale('th')->monthName;
            $year = $date->year + 543;

            $startOfMonth = $date->copy()->startOfMonth()->format('d');
            $endOfMonth = $date->copy()->endOfMonth()->format('d');

            $dateRangeText = "{$startOfMonth} {$monthName} {$year} - {$endOfMonth} {$monthName} {$year}";

            // ตรวจสอบไฟล์ template
            $templatePath = public_path('storage/templates/template-1.xls');
            if (!file_exists($templatePath)) {
                return back()->with('error', 'ไม่พบไฟล์ Template Excel ที่ตำแหน่ง: ' . $templatePath);
            }

            // โหลด template Excel
            $spreadsheet = IOFactory::load($templatePath);

            // ตรวจสอบและสร้างโฟลเดอร์สำหรับเก็บไฟล์ export
            $exportDir = storage_path('app/public/exports');
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // --------- สร้าง Sheet สำหรับโครงการปกติ ---------
            $regularSheet = $spreadsheet->getSheet(0);
            $regularSheet->setTitle('ปกติ');

            // เติมข้อมูลหัวเรื่อง
            $regularSheet->setCellValue('A1', 'แบบใบเบิกค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน');
            $regularSheet->setCellValue('A2', 'วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น');
            $regularSheet->setCellValue('A3', $semesterText);
            $regularSheet->setCellValue('A4', 'ประจำเดือน ' . $dateRangeText);

            // ตั้งค่า sheet ปกติ
            $regularSheet->setCellValue('C6', '(/) ภาคปกติ');
            $regularSheet->setCellValue('F6', '( ) โครงการพิเศษ');

            // ใส่ข้อมูลการสอนในตาราง (ถ้ามีข้อมูล)
            if ($regularAttendances->isNotEmpty()) {
                $row = 9;
                $rowNum = 1;

                foreach ($regularAttendances as $attendance) {
                    // ตรวจสอบประเภทข้อมูล
                    $isRegular = $attendance['type'] === 'regular';
                    $isExtra = $attendance['type'] === 'extra';
                    $isSpecial = $attendance['type'] === 'special';

                    // ดึงวันที่
                    if ($isRegular) {
                        $dateObj = \Carbon\Carbon::parse($attendance['data']->start_time);
                        $date = $dateObj->format('d-m-Y');
                    } elseif ($isExtra) {
                        $dateObj = \Carbon\Carbon::parse($attendance['data']->class_date);
                        $date = $dateObj->format('d-m-Y');
                    } else { // isSpecial
                        $dateObj = \Carbon\Carbon::parse($attendance['data']->start_work);
                        $date = $dateObj->format('d-m-Y');
                    }

                    // ดึงรหัสวิชา
                    $courseId = '';
                    if ($isRegular && isset($attendance['data']->class->course->subjects)) {
                        $courseId = $attendance['data']->class->course->subjects->subject_id ?? '-';
                    } elseif ($isExtra && isset($attendance['data']->class->course->subjects)) {
                        $courseId = $attendance['data']->class->course->subjects->subject_id ?? '-';
                    } elseif ($isSpecial && isset($attendance['data']->classes->course->subjects)) {
                        $courseId = $attendance['data']->classes->course->subjects->subject_id ?? '-';
                    }

                    // ดึงข้อมูลเวลา
                    $time = '';
                    if ($isRegular) {
                        $startTime = \Carbon\Carbon::parse($attendance['data']->start_time)->format('H:i');
                        $endTime = \Carbon\Carbon::parse($attendance['data']->end_time)->format('H:i');
                        $time = "{$startTime}-{$endTime}";
                    } elseif ($isExtra) {
                        $startTime = \Carbon\Carbon::parse($attendance['data']->start_time)->format('H:i');
                        $endTime = \Carbon\Carbon::parse($attendance['data']->end_time)->format('H:i');
                        $time = "{$startTime}-{$endTime}";
                    } else { // isSpecial
                        $startTime = \Carbon\Carbon::parse($attendance['data']->start_work)->format('H:i');
                        $duration = $attendance['data']->duration ?? 0;
                        $endTime = \Carbon\Carbon::parse($attendance['data']->start_work)
                            ->addMinutes($duration)->format('H:i');
                        $time = "{$startTime}-{$endTime}";
                    }

                    // ดึงชั่วโมง
                    $lectureHours = 0;
                    $labHours = 0;

                    if ($attendance['class_type'] === 'LECTURE') {
                        $lectureHours = $attendance['hours'];
                    } else {
                        $labHours = $attendance['hours'];
                    }

                    // ดึงหมายเหตุ
                    $note = '';
                    if ($isRegular && isset($attendance['data']->attendance)) {
                        $note = $attendance['data']->attendance->note ?? 'ตรวจงาน / เช็คชื่อ';
                    } elseif ($isExtra) {
                        $note = 'งานสอนชดเชย';
                    } else { // isSpecial
                        $note = $attendance['data']->detail ?? 'ตรวจงาน / เช็คชื่อ';
                    }

                    // ใส่ข้อมูลลงในตาราง
                    $regularSheet->setCellValue('A' . $row, $rowNum);

                    if ($rowNum === 1) {
                        $regularSheet->setCellValue('B' . $row, $student->name);
                        $regularSheet->setCellValue('C' . $row, in_array($student->degree_level, ['master', 'doctoral', 'graduate']) ? 'ป.โท/เอก' : 'ป.ตรี');
                    }

                    $regularSheet->setCellValue('D' . $row, $date);
                    $regularSheet->setCellValue('E' . $row, $courseId);
                    $regularSheet->setCellValue('F' . $row, $time);

                    if ($lectureHours > 0) {
                        $regularSheet->setCellValue('G' . $row, number_format($lectureHours, 2));
                    }

                    if ($labHours > 0) {
                        $regularSheet->setCellValue('H' . $row, number_format($labHours, 2));
                    }

                    $regularSheet->setCellValue('I' . $row, $note);

                    $row++;
                    $rowNum++;

                    if ($rowNum > 20) {
                        break; // จำกัดจำนวนแถวที่แสดง
                    }
                }
            }

            // ใส่ข้อมูลสรุป
            $totalRegularRow = 31;
            $regularSheet->setCellValue('G' . $totalRegularRow, number_format($regularLectureHours, 2));
            $regularSheet->setCellValue('H' . $totalRegularRow, number_format($regularLabHours, 2));

            // ใส่ข้อมูลค่าตอบแทน
            $rateRow = 35;
            $regularSheet->setCellValue('B' . $rateRow, number_format($regularLectureHours + $regularLabHours, 1));
            $regularSheet->setCellValue('E' . $rateRow, number_format($regularLectureRate, 2));
            $regularSheet->setCellValue('H' . $rateRow, number_format($regularPay, 2));

            // ใส่ข้อมูลสรุปรวม
            $totalRow = 39;
            $regularSheet->setCellValue('B' . $totalRow, number_format($regularPay, 2));

            // แปลงจำนวนเงินเป็นคำอ่าน
            if (class_exists('\App\Helpers\ThaiNumberHelper') && method_exists('\App\Helpers\ThaiNumberHelper', 'convertToText')) {
                $textBaht = \App\Helpers\ThaiNumberHelper::convertToText(number_format($regularPay, 2, '.', ''));
                $regularSheet->setCellValue('D' . $totalRow, '= ' . $textBaht . ' =');
            } else {
                // กรณีไม่พบคลาส ThaiNumberHelper
                Log::warning('ไม่พบคลาส ThaiNumberHelper::convertToText');
                $regularSheet->setCellValue('D' . $totalRow, '= (จำนวนเงินเป็นตัวอักษร) =');
            }

            // ใส่ข้อมูลจำนวนเงินในช่องหมายเหตุ
            $noteRow = 40;
            $regularSheet->setCellValue('B' . $noteRow, number_format($regularPay, 2));

            // ใส่ข้อมูลลายเซ็น
            $signatureRow = 44;
            $regularSheet->setCellValue('A' . $signatureRow, '(' . $student->name . ')');
            $regularSheet->setCellValue('D' . $signatureRow, '(' . $teacherFullTitle . ')');
            $regularSheet->setCellValue('G' . $signatureRow, '(ผศ. ดร.คำรณ สุนัติ)');

            // --------- สร้าง Sheet สำหรับโครงการพิเศษ (เฉพาะเมื่อมีข้อมูล) ---------
            if ($hasSpecialProject || $specialPay > 0) {
                // คัดลอก sheet ปกติแล้วแก้ไขเป็นโครงการพิเศษ
                $specialSheet = clone $spreadsheet->getSheet(0);
                $specialSheet->setTitle('พิเศษ');
                $spreadsheet->addSheet($specialSheet);

                // ตั้งค่า sheet พิเศษ
                $specialSheet->setCellValue('C6', '( ) ภาคปกติ');
                $specialSheet->setCellValue('F6', '(/) โครงการพิเศษ');

                // ล้างข้อมูลในตาราง
                for ($row = 9; $row <= 30; $row++) {
                    for ($col = 'A'; $col <= 'I'; $col++) {
                        $specialSheet->setCellValue($col . $row, '');
                    }
                }

                // ใส่ข้อมูลการสอนโครงการพิเศษในตาราง
                if ($specialAttendances->isNotEmpty()) {
                    $row = 9;
                    $rowNum = 1;

                    foreach ($specialAttendances as $attendance) {
                        // ตรวจสอบประเภทข้อมูล
                        $isRegular = $attendance['type'] === 'regular';
                        $isExtra = $attendance['type'] === 'extra';
                        $isSpecial = $attendance['type'] === 'special';

                        // ดึงวันที่
                        if ($isRegular) {
                            $dateObj = \Carbon\Carbon::parse($attendance['data']->start_time);
                            $date = $dateObj->format('d-m-Y');
                        } elseif ($isExtra) {
                            $dateObj = \Carbon\Carbon::parse($attendance['data']->class_date);
                            $date = $dateObj->format('d-m-Y');
                        } else { // isSpecial
                            $dateObj = \Carbon\Carbon::parse($attendance['data']->start_work);
                            $date = $dateObj->format('d-m-Y');
                        }

                        // ดึงรหัสวิชา
                        $courseId = '';
                        if ($isRegular && isset($attendance['data']->class->course->subjects)) {
                            $courseId = $attendance['data']->class->course->subjects->subject_id ?? '-';
                        } elseif ($isExtra && isset($attendance['data']->class->course->subjects)) {
                            $courseId = $attendance['data']->class->course->subjects->subject_id ?? '-';
                        } elseif ($isSpecial && isset($attendance['data']->classes->course->subjects)) {
                            $courseId = $attendance['data']->classes->course->subjects->subject_id ?? '-';
                        }

                        // ดึงข้อมูลเวลา
                        $time = '';
                        if ($isRegular) {
                            $startTime = \Carbon\Carbon::parse($attendance['data']->start_time)->format('H:i');
                            $endTime = \Carbon\Carbon::parse($attendance['data']->end_time)->format('H:i');
                            $time = "{$startTime}-{$endTime}";
                        } elseif ($isExtra) {
                            $startTime = \Carbon\Carbon::parse($attendance['data']->start_time)->format('H:i');
                            $endTime = \Carbon\Carbon::parse($attendance['data']->end_time)->format('H:i');
                            $time = "{$startTime}-{$endTime}";
                        } else { // isSpecial
                            $startTime = \Carbon\Carbon::parse($attendance['data']->start_work)->format('H:i');
                            $duration = $attendance['data']->duration ?? 0;
                            $endTime = \Carbon\Carbon::parse($attendance['data']->start_work)
                                ->addMinutes($duration)->format('H:i');
                            $time = "{$startTime}-{$endTime}";
                        }

                        // ดึงชั่วโมง
                        $lectureHours = 0;
                        $labHours = 0;

                        if ($attendance['class_type'] === 'LECTURE') {
                            $lectureHours = $attendance['hours'];
                        } else {
                            $labHours = $attendance['hours'];
                        }

                        // ดึงหมายเหตุ
                        $note = '';
                        if ($isRegular && isset($attendance['data']->attendance)) {
                            $note = $attendance['data']->attendance->note ?? 'ตรวจงาน / เช็คชื่อ';
                        } elseif ($isExtra) {
                            $note = 'งานสอนชดเชย';
                        } else { // isSpecial
                            $note = $attendance['data']->detail ?? 'ตรวจงาน / เช็คชื่อ';
                        }

                        // ใส่ข้อมูลลงในตาราง
                        $specialSheet->setCellValue('A' . $row, $rowNum);

                        if ($rowNum === 1) {
                            $specialSheet->setCellValue('B' . $row, $student->name);
                            $specialSheet->setCellValue('C' . $row, in_array($student->degree_level, ['master', 'doctoral', 'graduate']) ? 'ป.โท/เอก' : 'ป.ตรี');
                        }

                        $specialSheet->setCellValue('D' . $row, $date);
                        $specialSheet->setCellValue('E' . $row, $courseId);
                        $specialSheet->setCellValue('F' . $row, $time);

                        if ($lectureHours > 0) {
                            $specialSheet->setCellValue('G' . $row, number_format($lectureHours, 2));
                        }

                        if ($labHours > 0) {
                            $specialSheet->setCellValue('H' . $row, number_format($labHours, 2));
                        }

                        $specialSheet->setCellValue('I' . $row, $note);

                        $row++;
                        $rowNum++;

                        if ($rowNum > 20) {
                            break; // จำกัดจำนวนแถวที่แสดง
                        }
                    }
                }

                // ใส่ข้อมูลสรุป
                $specialSheet->setCellValue('G' . $totalRegularRow, number_format($specialLectureHours, 2));
                $specialSheet->setCellValue('H' . $totalRegularRow, number_format($specialLabHours, 2));

                // ใส่ข้อมูลค่าตอบแทน
                if ($isFixedPayment) {
                    // กรณีเหมาจ่าย
                    $specialSheet->setCellValue('A' . $rateRow, '- ปริญญาโท/เอก (เหมาจ่าย)');
                    $specialSheet->setCellValue('H' . $rateRow, number_format($specialPay, 2));
                } else {
                    // กรณีคิดตามชั่วโมง
                    $specialSheet->setCellValue('B' . $rateRow, number_format($specialLectureHours + $specialLabHours, 1));
                    $specialSheet->setCellValue('E' . $rateRow, number_format($specialLectureRate, 2));
                    $specialSheet->setCellValue('H' . $rateRow, number_format($specialPay, 2));
                }

                // ใส่ข้อมูลสรุปรวม
                $specialSheet->setCellValue('B' . $totalRow, number_format($specialPay, 2));

                // แปลงจำนวนเงินเป็นคำอ่าน
                if (class_exists('\App\Helpers\ThaiNumberHelper') && method_exists('\App\Helpers\ThaiNumberHelper', 'convertToText')) {
                    $textBaht = \App\Helpers\ThaiNumberHelper::convertToText(number_format($specialPay, 2, '.', ''));
                    $specialSheet->setCellValue('D' . $totalRow, '= ' . $textBaht . ' =');
                } else {
                    $specialSheet->setCellValue('D' . $totalRow, '= (จำนวนเงินเป็นตัวอักษร) =');
                }

                // ใส่ข้อมูลจำนวนเงินในช่องหมายเหตุ
                $specialSheet->setCellValue('B' . $noteRow, number_format($specialPay, 2));

                // ใส่ข้อมูลลายเซ็น
                $specialSheet->setCellValue('A' . $signatureRow, '(' . $student->name . ')');
                $specialSheet->setCellValue('D' . $signatureRow, '(' . $teacherFullTitle . ')');
                $specialSheet->setCellValue('G' . $signatureRow, '(ผศ. ดร.คำรณ สุนัติ)');
            }

            // บันทึกไฟล์ Excel
            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $fileName = 'TA-Compensation-' . $student->student_id . '-' . $selectedYearMonth . '.xls';
            $filePath = storage_path('app/public/exports/' . $fileName);

            $writer->save($filePath);

            // ส่งไฟล์ให้ดาวน์โหลดและลบหลังจากดาวน์โหลดเสร็จ
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้างไฟล์ Excel: ' . $e->getMessage());
        }
    }

    private function getFixedCompensationRate($teachingType, $degreeLevel)
    {
        $rate = CompensationRate::where('teaching_type', $teachingType)
            ->where('degree_level', $degreeLevel)
            ->where('status', 'active')
            ->where('is_fixed_payment', true)
            ->first();

        if ($rate) {
            return $rate->fixed_amount;
        }

        // ใช้ค่าเริ่มต้นถ้าไม่พบอัตราในฐานข้อมูล
        if ($degreeLevel === 'graduate' && $teachingType === 'special') {
            return 4000; // ผู้ช่วยสอน บัณฑิต ที่สอน ภาคพิเศษ เหมาจ่ายรายเดือน
        }

        return null;
    }
}
