<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Students;
use App\Models\Subjects;
use App\Models\Teachers;
use App\Models\Attendances;
use App\Models\ExtraAttendances;
use Illuminate\Http\Request;
use App\Models\CourseTas;
use App\Models\Classes;
use App\Models\Requests;
use App\Models\Teaching;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestsDetail;
use App\Models\TeacherRequestStudent;
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


    public function indexTARequests()
    {
        $teacher = Auth::user()->teacher;

        $courses = Courses::where('owner_teacher_id', $teacher->teacher_id)
            ->with(['subjects', 'course_tas.student'])
            ->get()
            ->map(function ($course) {
                $pendingRequest = TeacherRequest::where('course_id', $course->course_id)
                    ->where('status', 'P')
                    ->latest()
                    ->first();

                return [
                    'course' => $course,
                    'pending_request' => $pendingRequest
                ];
            });

        $requests = TeacherRequest::where('teacher_id', $teacher->teacher_id)
            ->with([
                'details.students',
                'course.subjects'
            ])
            ->latest()
            ->get();

        return view('layouts.teacher.ta-request.index', compact('courses', 'requests'));
    }

    public function createTARequest($course_id)
    {
        $course = Courses::with(['subjects', 'course_tas.student'])->findOrFail($course_id);

        $availableStudents = Students::whereIn('id', function ($query) use ($course_id) {
            $query->select('student_id')
                ->from('course_tas')
                ->where('course_id', $course_id);
        })->get();

        \Log::info('Course: ' . json_encode($course));
        \Log::info('Available Students: ' . json_encode($availableStudents));

        return view('layouts.teacher.ta-request.create', compact('course', 'availableStudents'));
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
                'status' => 'P',
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
            \Log::error('Error in showTARequest: ' . $e->getMessage());
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

    public function taDetail($ta_id)
    {
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

            // ดึงข้อมูลการลงเวลาปกติ
            $teachings = Teaching::with([
                'attendance' => function ($query) {
                    $query->select('id', 'teaching_id', 'student_id', 'status', 'note', 'approve_status', 'approve_note');
                },
                'teacher',
                'class'
            ])
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereBetween('start_time', [$start, $end])
                ->whereYear('start_time', $selectedDate->year)
                ->whereMonth('start_time', $selectedDate->month)
                ->whereHas('attendance')
                ->get();

            // ดึงข้อมูลการลงเวลาพิเศษ
            $extraAttendances = ExtraAttendances::where('student_id', $student->id)
                ->where('class_id', 'LIKE', $ta->course_id . '%')
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->with([
                    'classes' => function ($query) {
                        $query->with('teachers');
                    }
                ])
                ->get();

            // ตรวจสอบว่าเดือนนี้มีการอนุมัติแล้วหรือไม่
            $isMonthApproved = false;
            $approvalNote = null;

            // ตรวจสอบจากทั้งสองตาราง
            $normalAttendanceApproved = Attendances::where('student_id', $student->id)
                ->whereYear('created_at', $selectedDate->year)
                ->whereMonth('created_at', $selectedDate->month)
                ->where('approve_status', 'a')
                ->exists();

            $extraAttendanceApproved = ExtraAttendances::where('student_id', $student->id)
                ->whereYear('start_work', $selectedDate->year)
                ->whereMonth('start_work', $selectedDate->month)
                ->where('approve_status', 'a')
                ->exists();

            // ถ้าทั้งสองตารางมีการอนุมัติแล้ว
            if ($normalAttendanceApproved && $extraAttendanceApproved) {
                $isMonthApproved = true;

                // ดึงหมายเหตุการอนุมัติล่าสุดจากทั้งสองตาราง
                $latestNormalApproval = Attendances::where('student_id', $student->id)
                    ->whereYear('created_at', $selectedDate->year)
                    ->whereMonth('created_at', $selectedDate->month)
                    ->where('approve_status', 'a')
                    ->latest()
                    ->first();

                $latestExtraApproval = ExtraAttendances::where('student_id', $student->id)
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->where('approve_status', 'a')
                    ->latest()
                    ->first();

                // เลือกหมายเหตุจากรายการที่อนุมัติล่าสุด
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

            return view('layouts.teacher.taDetail', compact(
                'student',
                'semester',
                'teachings',
                'extraAttendances',
                'monthsInSemester',
                'selectedYearMonth',
                'isMonthApproved',
                'approvalNote'
            ));

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    public function approveMonthlyAttendance(Request $request, $ta_id)
    {
        try {
            $yearMonth = $request->input('year_month');
            $approveNote = $request->input('approve_note'); // เปลี่ยนเป็น approve_note
            $selectedDate = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);
            $ta = CourseTas::with('student')->findOrFail($ta_id);

            DB::beginTransaction();

            try {
                // อนุมัติการลงเวลาปกติ
                $normalAttendances = Attendances::whereHas('teaching', function ($query) use ($ta, $selectedDate) {
                    $query->where('class_id', 'LIKE', $ta->course_id . '%')
                        ->whereYear('start_time', $selectedDate->year)
                        ->whereMonth('start_time', $selectedDate->month);
                })
                    ->where('student_id', $ta->student_id)
                    ->where(function ($query) {
                        $query->whereNull('approve_status')
                            ->orWhere('approve_status', '!=', 'a');
                    })
                    ->get();

                foreach ($normalAttendances as $attendance) {
                    $attendance->update([
                        'approve_status' => 'a',
                        'approve_at' => now(),
                        'approve_user_id' => auth()->id(),
                        'approve_note' => $approveNote
                    ]);
                }

                // อนุมัติการลงเวลาพิเศษ
                $extraAttendances = ExtraAttendances::where('student_id', $ta->student_id)
                    ->where('class_id', 'LIKE', $ta->course_id . '%')
                    ->whereYear('start_work', $selectedDate->year)
                    ->whereMonth('start_work', $selectedDate->month)
                    ->where(function ($query) {
                        $query->whereNull('approve_status')
                            ->orWhere('approve_status', '!=', 'a');
                    })
                    ->get();

                foreach ($extraAttendances as $extraAttendance) {
                    $extraAttendance->update([
                        'approve_status' => 'a',
                        'approve_at' => now(),
                        'approve_user_id' => auth()->id(),
                        'approve_note' => $approveNote
                    ]);
                }

                if ($normalAttendances->isEmpty() && $extraAttendances->isEmpty()) {
                    return back()->with('error', 'ไม่พบข้อมูลการลงเวลาที่รออนุมัติสำหรับเดือนที่เลือก');
                }

                DB::commit();
                return back()->with('success', 'อนุมัติการลงเวลาประจำเดือนเรียบร้อยแล้ว');

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
