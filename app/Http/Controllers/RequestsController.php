<?php



namespace App\Http\Controllers;

use App\Models\CourseTas;
use App\Models\CourseTaClasses;
use App\Models\Classes;
use App\Models\Semesters;
use App\Models\Subjects;
use App\Models\Courses;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Requests;
use App\Services\TDBMApiService;

class RequestsController extends Controller
{
    protected $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->tdbmService = $tdbmService;
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

    public function showTARequests()
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->back()->with('error', 'ไม่พบข้อมูลนักศึกษาสำหรับผู้ใช้นี้');
            }

            // ดึงภาคการศึกษาล่าสุด
            $currentSemester = $this->getActiveSemester();

            if (!$currentSemester) {
                return redirect()->back()->with('error', 'ไม่พบข้อมูลภาคการศึกษา');
            }

            // ดึงข้อมูลจากฐานข้อมูลภายใน
            $subjects = Subjects::all();
            $courses = Courses::where('semester_id', $currentSemester->semester_id)->get();
            $studentClasses = Classes::where('semester_id', $currentSemester->semester_id)->get();

            // จัดรูปแบบข้อมูลวิชาและเซคชัน
            $subjectsWithSections = $subjects
                ->filter(function ($subject) use ($courses) {
                    return $courses->where('subject_id', $subject->subject_id)->isNotEmpty();
                })
                ->map(function ($subject) use ($courses, $studentClasses) {
                    $course = $courses->where('subject_id', $subject->subject_id)->first();
                    $sections = $studentClasses->where('course_id', $course->course_id)
                        ->pluck('section_num')
                        ->unique()
                        ->values()
                        ->toArray();

                    return [
                        'subject' => [
                            'subject_id' => $subject->subject_id,
                            'subject_name_en' => $subject->name_en,
                            'subject_name_th' => $subject->name_th
                        ],
                        'sections' => $sections
                    ];
                })->values();

            // ดึงข้อมูลคำขอสำหรับภาคการศึกษาที่กำหนดเท่านั้น
            $requests = CourseTas::with([
                'student',
                'course.subjects',
                'course.semesters', // เพิ่มความสัมพันธ์กับ semesters
                'courseTaClasses.requests' => function ($query) {
                    $query->latest();
                }
            ])
                ->where('student_id', $student->id)
                ->whereHas('course', function ($query) use ($currentSemester) {
                    $query->where('semester_id', $currentSemester->semester_id);
                }) // เพิ่มการกรองด้วย semester_id
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

            return view('layouts.ta.statusRequest', compact('requests', 'subjectsWithSections'));
        } catch (\Exception $e) {
            Log::error('Error in showTARequests: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการแสดงข้อมูล');
        }
    }

    public function update(Request $request, $studentId)
    {
        try {
            $request->validate([
                'subject_id' => 'required|string',
                'sections' => 'required|array|min:1',
                'sections.*' => 'required|integer'
            ]);

            DB::beginTransaction();

            // ตรวจสอบนักศึกษา
            $student = Students::where('student_id', $studentId)->first();
            if (!$student) {
                throw new \Exception("ไม่พบข้อมูลนักศึกษารหัส {$studentId}");
            }

            // ดึงภาคการศึกษาล่าสุด
            $currentSemester = Semesters::orderBy('year', 'desc')
                ->orderBy('semesters', 'desc')
                ->first();

            if (!$currentSemester) {
                throw new \Exception('ไม่พบข้อมูลภาคการศึกษา');
            }

            // ตรวจสอบข้อมูลรายวิชา
            $course = Courses::where('semester_id', $currentSemester->semester_id)
                ->where('subject_id', $request->subject_id)
                ->first();

            if (!$course) {
                throw new \Exception('ไม่พบข้อมูลรายวิชาที่เลือก');
            }

            // ค้นหา CourseTa ที่มีอยู่
            $existingCourseTa = CourseTas::where('student_id', $student->id)->first();
            if (!$existingCourseTa) {
                throw new \Exception('ไม่พบคำขอที่ต้องการอัพเดต');
            }

            // ลบข้อมูลเก่า
            foreach ($existingCourseTa->courseTaClasses as $taClass) {
                $taClass->requests()->delete();
            }
            $existingCourseTa->courseTaClasses()->delete();

            // อัพเดตข้อมูล course_id
            $existingCourseTa->course_id = $course->course_id;
            $existingCourseTa->save();

            // สร้างข้อมูลใหม่สำหรับแต่ละเซคชัน
            foreach ($request->sections as $sectionNum) {
                $class = Classes::where('course_id', $course->course_id)
                    ->where('section_num', $sectionNum)
                    ->first();

                if (!$class) {
                    throw new \Exception('ไม่พบข้อมูลกลุ่มเรียนที่เลือก');
                }

                $taClass = CourseTaClasses::create([
                    'class_id' => $class->class_id,
                    'course_ta_id' => $existingCourseTa->id
                ]);

                Requests::create([
                    'course_ta_class_id' => $taClass->id,
                    'status' => 'W',
                    'approved_at' => null,
                    'comment' => null
                ]);
            }

            DB::commit();
            Toastr()->success('อัพเดตคำร้องสำเร็จ', 'สำเร็จ!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error:', [
                'message' => $e->getMessage(),
                'student_id' => $studentId,
                'subject_id' => $request->subject_id,
                'sections' => $request->sections
            ]);

            Toastr()->error('เกิดข้อผิดพลาด: ' . $e->getMessage(), 'ผิดพลาด!');
            return redirect()->back();
        }
    }

    public function destroy($studentId)
    {
        try {
            DB::beginTransaction();

            $student = Students::where('student_id', $studentId)->first();
            if (!$student) {
                throw new \Exception('ไม่พบข้อมูลนักศึกษา');
            }

            $courseTa = CourseTas::with(['courseTaClasses.requests' => function ($query) {
                $query->latest();
            }])
                ->where('student_id', $student->id)
                ->first();

            if (!$courseTa) {
                throw new \Exception('ไม่พบคำร้องที่ต้องการลบ');
            }

            $latestRequest = $courseTa->courseTaClasses
                ->flatMap->requests
                ->sortByDesc('created_at')
                ->first();

            if (!$latestRequest || $latestRequest->status !== 'W') {
                throw new \Exception('ไม่สามารถลบคำร้องที่ดำเนินการแล้วได้');
            }

            foreach ($courseTa->courseTaClasses as $taClass) {
                $taClass->requests()->delete();
            }
            $courseTa->courseTaClasses()->delete();
            $courseTa->delete();

            DB::commit();
            Toastr()->success('ลบคำร้องสำเร็จ', 'สำเร็จ!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete request error:', [
                'message' => $e->getMessage(),
                'student_id' => $studentId
            ]);

            Toastr()->error('เกิดข้อผิดพลาด: ' . $e->getMessage(), 'ผิดพลาด!');
            return redirect()->back();
        }
    }
}
