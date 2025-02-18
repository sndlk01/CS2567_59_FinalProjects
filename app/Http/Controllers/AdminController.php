<?php

namespace App\Http\Controllers;
use App\Models\Announce;
use App\Models\Courses;
use App\Models\Students;
use App\Models\CourseTas;
use App\Models\Disbursements;
use App\Models\Teaching;
use App\Models\Classes;
use App\Models\ExtraAttendances;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestsDetail;
use App\Models\TeacherRequestStudent;
use App\Models\CourseTaClasses;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Requests;


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


    /// ADMIN ROLE

    public function taUsers()
    {
        $coursesWithTAs = Courses::whereHas('course_tas.courseTaClasses.requests', function ($query) {
            $query->where('status', 'A')  // เช็คสถานะว่าอนุมัติ
                ->whereNotNull('approved_at');  // และมีวันที่อนุมัติ
        })
            ->with([
                'subjects',  // ข้อมูลวิชา
                'teachers',  // ข้อมูลอาจารย์
                'course_tas.student',  // ข้อมูลนักศึกษา TA
                'course_tas.courseTaClasses.requests' => function ($query) {
                    $query->where('status', 'A')
                        ->whereNotNull('approved_at');
                }
            ])
            ->get();

        // Debug ข้อมูล
        Log::info('จำนวนรายวิชาที่มี TA: ' . $coursesWithTAs->count());

        return view('layouts.admin.taUsers', compact('coursesWithTAs'));
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

            // ตรวจสอบว่าไฟล์มีอยู่จริง
            // $filePath = storage_path('public' . $disbursement->file_path);
            if (!Storage::exists('public' . $disbursement->file_path)) {
                return back()->with('error', 'ไม่พบไฟล์เอกสาร');
            }

            return Storage::disk('public')->download($disbursement->uploadfile);
        } catch (\Exception $e) {
            Log::error('Document download error: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดาวน์โหลดเอกสาร');
        }
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
            $attendanceType = request('type', 'all');

            $allAttendances = collect();
            $regularHours = 0;
            $specialHours = 0;

            // Get regular attendances
            if ($attendanceType === 'all' || $attendanceType === 'regular') {
                $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects'])
                    ->where('class_id', 'LIKE', $ta->course_id . '%')
                    ->whereYear('start_time', $selectedDate->year)
                    ->whereMonth('start_time', $selectedDate->month)
                    ->whereHas('attendance', function ($query) {
                        $query->where('approve_status', 'A');
                    })
                    ->get()->groupBy(function ($teaching) {
                        return $teaching->class->section_num;
                    });

                foreach ($teachings as $section => $sectionTeachings) {
                    foreach ($sectionTeachings as $teaching) {
                        $startTime = \Carbon\Carbon::parse($teaching->start_time);
                        $endTime = \Carbon\Carbon::parse($teaching->end_time);
                        $hours = $endTime->diffInMinutes($startTime) / 60;
                        $regularHours += $hours;

                        $allAttendances->push([
                            'type' => 'regular',
                            'section' => $section,
                            'date' => $teaching->start_time,
                            'data' => $teaching,
                            'hours' => $hours
                        ]);
                    }
                }
            }

            // Get extra attendances
            if ($attendanceType === 'all' || $attendanceType === 'special') {
                $extraAttendances = ExtraAttendances::with(['classes.course.subjects'])
                    ->where('student_id', $student->id)
                    ->where('approve_status', 'A')
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->get()
                    ->groupBy('class_id');

                foreach ($extraAttendances as $classId => $extras) {
                    $class = Classes::find($classId);
                    foreach ($extras as $extra) {
                        $hours = $extra->duration / 60;
                        $specialHours += $hours;

                        $allAttendances->push([
                            'type' => 'special',
                            'section' => $class ? $class->section_num : 'N/A',
                            'date' => $extra->start_work,
                            'data' => $extra,
                            'hours' => $hours
                        ]);
                    }
                }
            }

            // คำนวณค่าตอบแทน
            $regularPayRate = 40; // บาทต่อชั่วโมง
            $specialPayRate = 50; // บาทต่อชั่วโมง

            $regularPay = $regularHours * $regularPayRate;
            $specialPay = $specialHours * $specialPayRate;
            $totalPay = $regularPay + $specialPay;

            // เพิ่มข้อมูลการคำนวณเข้าไปใน view
            $compensation = [
                'regularHours' => $regularHours,
                'specialHours' => $specialHours,
                'regularPay' => $regularPay,
                'specialPay' => $specialPay,
                'totalPay' => $totalPay
            ];

            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            return view('layouts.admin.detailsById', compact(
                'student',
                'semester',
                'attendancesBySection',
                'monthsInSemester',
                'selectedYearMonth',
                'attendanceType',
                'compensation'  // ส่งข้อมูลการคำนวณไปยัง view
            ));

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function exportTaDetailPDF($id)
    {
        try {
            $selectedYearMonth = request('month');
            $attendanceType = request('type', 'all');

            $ta = CourseTas::with(['student', 'course.semesters'])
                ->whereHas('student', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->first();

            if (!$ta) {
                return back()->with('error', 'ไม่พบข้อมูลผู้ช่วยสอน');
            }

            $student = $ta->student;
            $semester = $ta->course->semesters;
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedYearMonth);

            $monthText = $selectedDate->locale('th')->monthName . ' ' . ($selectedDate->year + 543);
            $year = $selectedDate->year + 543;

            // ตัวแปรสำหรับเก็บชั่วโมงแยกตามประเภท
            $regularBroadcastHours = 0;  // ชั่วโมงบรรยาย
            $regularLabHours = 0;        // ชั่วโมงปฏิบัติการ
            $allAttendances = collect();

            // Get regular attendances
            if ($attendanceType === 'all' || $attendanceType === 'regular') {
                $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects'])
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

                        if ($teaching->class_type === 'L') {
                            $regularLabHours += $hours;  // เพิ่มชั่วโมงปฏิบัติการ
                        } else {
                            $regularBroadcastHours += $hours;  // เพิ่มชั่วโมงบรรยาย
                        }

                        $allAttendances->push([
                            'type' => 'regular',
                            'section' => $section,
                            'date' => $teaching->start_time,
                            'data' => $teaching,
                            'hours' => $hours
                        ]);
                    }
                }
            }

            $regularBroadcastHours = 0;   // ชั่วโมงบรรยายปกติ
            $regularLabHours = 0;         // ชั่วโมงปฏิบัติการปกติ
            $specialTeachingHours = 0;    // ชั่วโมงสอนพิเศษ
            $allAttendances = collect();

            // Get regular attendances
            if ($attendanceType === 'all' || $attendanceType === 'regular') {
                $teachings = Teaching::with(['attendance', 'teacher', 'class.course.subjects'])
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

                        if ($teaching->class_type === 'L') {
                            $regularLabHours += $hours;
                        } else {
                            $regularBroadcastHours += $hours;
                        }

                        $allAttendances->push([
                            'type' => 'regular',
                            'section' => $section,
                            'date' => $teaching->start_time,
                            'data' => $teaching,
                            'hours' => $hours
                        ]);
                    }
                }
            }

            // Get extra/special attendances
            if ($attendanceType === 'all' || $attendanceType === 'special') {
                $extraAttendances = ExtraAttendances::with(['classes.course.subjects'])
                    ->where('student_id', $student->id)
                    ->where('approve_status', 'A')
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->get()
                    ->groupBy('class_id');

                foreach ($extraAttendances as $classId => $extras) {
                    foreach ($extras as $extra) {
                        $hours = $extra->duration / 60;
                        $specialTeachingHours += $hours;  // นับเป็นชั่วโมงพิเศษทั้งหมด

                        $allAttendances->push([
                            'type' => 'special',
                            'section' => $extra->classes ? $extra->classes->section_num : 'N/A',
                            'date' => $extra->start_work,
                            'data' => $extra,
                            'hours' => $hours
                        ]);
                    }
                }
            }

            // คำนวณค่าตอบแทน
            $regularPay = ($regularBroadcastHours + $regularLabHours) * 40;  // ชั่วโมงปกติ 40 บาท/ชั่วโมง
            $specialPay = $specialTeachingHours * 50;                        // ชั่วโมงพิเศษ 50 บาท/ชั่วโมง
            $totalPay = $regularPay + $specialPay;

            $totalPayText = $this->convertNumberToThaiBaht($totalPay);

            $attendancesBySection = $allAttendances->sortBy('date')->groupBy('section');

            // สร้าง PDF
            $pdf = PDF::loadView('exports.detailPDF', compact(
                'student',
                'semester',
                'attendancesBySection',
                'selectedYearMonth',
                'monthText',
                'year',
                'regularBroadcastHours',
                'regularLabHours',
                'specialTeachingHours',
                'regularPay',
                'specialPay',
                'totalPay',
                'totalPayText'
            ));

            $pdf->setPaper('A4');
            $fileName = 'TA-Compensation-' . $student->student_id . '-' . $selectedYearMonth . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
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
            // เพิ่ม import Teachers model และใช้งานผ่าน eager loading
            $requests = TeacherRequest::with([
                'teacher:teacher_id,name,email', // เลือกเฉพาะ fields ที่ต้องการ
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