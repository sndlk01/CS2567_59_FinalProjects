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
    Semesters
};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};



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

        // Accept Both
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

    // public function taUsers()
    // {
    //     $coursesWithTAs = Courses::whereHas('course_tas.courseTaClasses.requests', function ($query) {
    //         $query->where('status', 'A')  // เช็คสถานะว่าอนุมัติ
    //             ->whereNotNull('approved_at');  // และมีวันที่อนุมัติ
    //     }) 
    //         ->with([
    //             'subjects',  // ข้อมูลวิชา
    //             'teachers',  // ข้อมูลอาจารย์
    //             'course_tas.student',  // ข้อมูลนักศึกษา TA
    //             'course_tas.courseTaClasses.requests' => function ($query) {
    //                 $query->where('status', 'A')
    //                     ->whereNotNull('approved_at');
    //             }
    //         ])
    //         ->get();

    //     // Debug ข้อมูล
    //     Log::info('จำนวนรายวิชาที่มี TA: ' . $coursesWithTAs->count());

    //     return view('layouts.admin.taUsers', compact('coursesWithTAs'));
    // }


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

            // Debug ข้อมูล
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

    // ฟังก์ชันสำหรับบันทึกค่า semester ที่เลือกลงในฐานข้อมูล
    private function updateActiveSemester($semesterId)
    {
        try {
            // สร้างหรืออัปเดตตารางการตั้งค่า
            // สมมติว่ามีตาราง settings สำหรับเก็บการตั้งค่าของระบบ
            DB::table('setting_semesters')->updateOrInsert(
                ['key' => 'active_semester_id'],
                ['value' => $semesterId, 'updated_at' => now()]
            );

            // บันทึกลง session ด้วยเพื่อให้ controller อื่นๆ สามารถเข้าถึงได้
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

            // ตรวจสอบว่ามี semester ที่เลือกจริงหรือไม่
            $semester = Semesters::find($semesterId);
            if (!$semester) {
                return back()->with('error', 'ไม่พบภาคการศึกษาที่เลือก');
            }

            // บันทึกค่าลงในตาราง setting_semesters สำหรับผู้ใช้
            DB::table('setting_semesters')->updateOrInsert(
                ['key' => 'user_active_semester_id'],
                ['value' => $semesterId, 'updated_at' => now()]
            );

            // บันทึกลง session
            session(['user_active_semester_id' => $semesterId]);

            return back()->with('success', 'บันทึกการเลือกภาคการศึกษาสำหรับผู้ใช้งานเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Log::error('Error updating user semester: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }
    }

    // เพิ่มฟังก์ชันสำหรับดึงค่า semester ที่เลือก
    public function getActiveSemester()
    {
        try {
            // ลองดึงจาก session ก่อน
            $activeSemesterId = session('active_semester_id');

            // ถ้าไม่มีใน session ให้ดึงจากฐานข้อมูล
            if (!$activeSemesterId) {
                $setting = DB::table('setting_semesters')->where('key', 'active_semester_id')->first();

                if ($setting) {
                    $activeSemesterId = $setting->value;
                    session(['active_semester_id' => $activeSemesterId]);
                } else {
                    // ถ้าไม่มีค่าในฐานข้อมูล ให้ใช้ semester ล่าสุด
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


    private function getCompensationRate($teachingType, $classType)
    {
        $rate = CompensationRate::getActiveRate($teachingType, $classType);

        if (!$rate) {
            // ถ้าไม่พบอัตราที่เฉพาะเจาะจง ให้ดึงอัตราค่าเริ่มต้น
            if ($teachingType === 'regular') {
                if ($classType === 'LECTURE') {
                    return 40; // ค่าเริ่มต้นสำหรับการสอนบรรยายภาคปกติ
                } else { // LAB
                    return 40; // ค่าเริ่มต้นสำหรับการสอนปฏิบัติการภาคปกติ
                }
            } else { // special
                if ($classType === 'LECTURE') {
                    return 50; // ค่าเริ่มต้นสำหรับการสอนบรรยายภาคพิเศษ
                } else { // LAB
                    return 50; // ค่าเริ่มต้นสำหรับการสอนปฏิบัติการภาคพิเศษ
                }
            }
        }

        return $rate->rate_per_hour;
    }

    public function taDetail($student_id)
    {
        try {
            $ta = CourseTas::with(['student', 'course.semesters'])
                ->where('student_id', $student_id)
                ->firstOrFail();

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
                // ดึงข้อมูลการสอนปกติ
                $teachings = Teaching::with([
                    'attendance',
                    'teacher',
                    'class.course.subjects',
                    'class.major'
                ])
                    ->where('class_id', 'LIKE', $ta->course_id . '%')
                    ->whereYear('start_time', $selectedDate->year)
                    ->whereMonth('start_time', $selectedDate->month)
                    ->whereHas('attendance', function ($query) {
                        $query->where('approve_status', 'A');
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

                foreach ($teachings as $section => $sectionTeachings) {
                    foreach ($sectionTeachings as $teaching) {
                        $startTime = \Carbon\Carbon::parse($teaching->start_time);
                        $endTime = \Carbon\Carbon::parse($teaching->end_time);
                        $hours = $endTime->diffInMinutes($startTime) / 60;

                        $majorType = $teaching->class->major->major_type ?? 'N';
                        $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                        // คำนวณชั่วโมงตามประเภท
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
            }

            if ($attendanceType === 'all' || $attendanceType === 'N' || $attendanceType === 'S') {
                // ดึงข้อมูลการสอนพิเศษ
                $extraAttendances = ExtraAttendances::with([
                    'classes.course.subjects',
                    'classes.major'
                ])
                    ->where('student_id', $student->id)
                    ->where('approve_status', 'A')
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->when($attendanceType !== 'all', function ($query) use ($attendanceType) {
                        $query->whereHas('classes.major', function ($q) use ($attendanceType) {
                            $q->where('major_type', $attendanceType);
                        });
                    })
                    ->get()
                    ->groupBy('class_id');

                foreach ($extraAttendances as $classId => $extras) {
                    foreach ($extras as $extra) {
                        $hours = $extra->duration / 60;
                        $class = $extra->classes;

                        $majorType = $class && $class->major ? $class->major->major_type : 'N';
                        $classType = $extra->class_type === 'L' ? 'LAB' : 'LECTURE';

                        // คำนวณชั่วโมงตามประเภท
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
                            'type' => 'special',
                            'section' => $class ? $class->section_num : 'N/A',
                            'date' => $extra->start_work,
                            'data' => $extra,
                            'hours' => $hours,
                            'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                            'class_type' => $classType
                        ]);
                    }
                }
            }

            // ดึงอัตราค่าตอบแทนจากฐานข้อมูล
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

            // เก็บข้อมูลค่าตอบแทนเพื่อส่งไปยัง view
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

            // จัดกลุ่มข้อมูลการลงเวลาตาม section เพื่อแสดงผล
            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            return view('layouts.admin.detailsById', compact(
                'student',
                'semester',
                'attendancesBySection',
                'monthsInSemester',
                'selectedYearMonth',
                'attendanceType',
                'compensation'
            ));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
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

        // ดึงข้อมูลการลงเวลาปกติ
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

        // ดึงข้อมูลการลงเวลาเพิ่มเติม
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

            // ดึงข้อมูลอาจารย์ผู้สอนจากข้อมูลรายวิชา
            $teacherName = $ta->course->teachers->name ?? 'อาจารย์ผู้สอน';
            $teacherPosition = $ta->course->teachers->position ?? '';
            $teacherDegree = $ta->course->teachers->degree ?? '';

            // รวมคำนำหน้า ตำแหน่ง วุฒิ และชื่ออาจารย์
            $teacherFullTitle = trim($teacherPosition . ' ' . $teacherDegree . '.' . ' ' . $teacherName);

            // ใช้ค่าคงที่สำหรับชื่อหัวหน้าสาขาแทนการดึงจากฐานข้อมูล
            $headName = 'ผศ.ดร.สายยัญ สายยศ';

            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            // Format current date in Thai style
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

            // ตัวแปรสำหรับเก็บข้อมูลการสอนทั้งหมด
            $allAttendances = collect();

            // ตัวแปรสำหรับตรวจสอบว่ามีข้อมูลโครงการปกติหรือโครงการพิเศษหรือไม่
            $hasRegularProject = false;
            $hasSpecialProject = false;

            // ดึงข้อมูลการสอนปกติ (regular teaching)
            $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects', 'class.major'])
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereYear('start_time', $selectedDate->year)
                ->whereMonth('start_time', $selectedDate->month)
                ->whereHas('attendance', function ($query) {
                    $query->where('approve_status', 'A');
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

                    // ตรวจสอบประเภทของคลาส (ปกติหรือพิเศษ)
                    $majorType = $teaching->class->major->major_type ?? 'N';
                    $classType = $teaching->class_type === 'L' ? 'LAB' : 'LECTURE';

                    // ตรวจสอบและบันทึกการมีข้อมูลของแต่ละโครงการ
                    if ($majorType === 'N') {
                        $hasRegularProject = true;
                    } else {
                        $hasSpecialProject = true;
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

            // ดึงข้อมูลการสอนพิเศษ (extra teaching)
            $extraAttendances = ExtraAttendances::with(['classes.course.subjects', 'classes.major'])
                ->where('student_id', $student->id)
                ->where('approve_status', 'A')
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->get()
                ->groupBy('class_id');

            foreach ($extraAttendances as $classId => $extras) {
                foreach ($extras as $extra) {
                    $hours = $extra->duration / 60;
                    $class = $extra->classes;

                    // ตรวจสอบประเภทของคลาส (ปกติหรือพิเศษ)
                    $majorType = $class && $class->major ? $class->major->major_type : 'N';
                    $classType = $extra->class_type === 'L' ? 'LAB' : 'LECTURE';

                    // ตรวจสอบและบันทึกการมีข้อมูลของแต่ละโครงการ
                    if ($majorType === 'N') {
                        $hasRegularProject = true;
                    } else {
                        $hasSpecialProject = true;
                    }

                    $allAttendances->push([
                        'type' => 'special',
                        'section' => $class ? $class->section_num : 'N/A',
                        'date' => $extra->start_work,
                        'data' => $extra,
                        'hours' => $hours,
                        'teaching_type' => $majorType === 'N' ? 'regular' : 'special',
                        'class_type' => $classType
                    ]);
                }
            }

            // ดึงอัตราค่าตอบแทนจากฐานข้อมูล
            $regularRate = $this->getCompensationRate('regular', 'LECTURE'); // ใช้อัตราเดียวกันสำหรับภาคปกติทั้งหมด
            $specialRate = $this->getCompensationRate('special', 'LECTURE'); // ใช้อัตราเดียวกันสำหรับภาคพิเศษทั้งหมด

            // จัดเรียงข้อมูลการลงเวลาตาม section
            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            // สร้างข้อมูลอัตราค่าตอบแทนสำหรับส่งไปยัง view
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
                'teacherName',
                'teacherPosition',
                'teacherDegree',
                'teacherFullTitle',
                'hasRegularProject',  // เพิ่มตัวแปรสำหรับตรวจสอบว่ามีข้อมูลโครงการปกติหรือไม่
                'hasSpecialProject'   // เพิ่มตัวแปรสำหรับตรวจสอบว่ามีข้อมูลโครงการพิเศษหรือไม่
            ));

            $pdf->setPaper('A4');
            $fileName = 'TA-Compensation-' . $student->student_id . '-' . $selectedYearMonth . '.pdf';

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
    public function index()
    {
        $announces = Announce::latest()->paginate(5);
        return view('layouts.admin.index', compact('announces'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.admin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        Announce::create($request->all());

        return redirect()
            ->route('announces.index')
            ->with('success', 'announce created successfully.');
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
        return view('layouts.admin.edit', compact('announce'));
    }

    public function update(Request $request, Announce $announce)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $announce->update($request->all());

        return redirect()
            ->route('announces.index')
            ->with('success', 'announce updated successfully');
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

            // Update main request status
            $taRequest->update([
                'status' => $validated['status'],
                'admin_processed_at' => now(),
                'admin_user_id' => Auth::id(),
                'admin_comment' => $validated['comment'] ?? null
            ]);

            // For each student in the request, update their request status
            foreach ($taRequest->details as $detail) {
                foreach ($detail->students as $student) {
                    // หา courseTaClasses ที่มีอยู่แล้ว
                    $courseTaClasses = CourseTaClasses::where('course_ta_id', $student->course_ta_id)->get();

                    foreach ($courseTaClasses as $courseTaClass) {
                        // อัพเดตหรือสร้าง request ใหม่
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
}
