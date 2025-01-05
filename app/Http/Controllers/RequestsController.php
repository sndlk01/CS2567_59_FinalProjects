<?php



namespace App\Http\Controllers;

use App\Models\CourseTas;
use App\Models\CourseTaClasses;
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

    public function showTARequests()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลนักศึกษาสำหรับผู้ใช้นี้');
        }

        // Get current semester
        $currentSemester = collect($this->tdbmService->getSemesters())
            ->filter(function ($semester) {
                $startDate = \Carbon\Carbon::parse($semester['start_date']);
                $endDate = \Carbon\Carbon::parse($semester['end_date']);
                return now()->between($startDate, $endDate);
            })->first();

        // Get subjects with sections for the current semester
        $subjects = collect($this->tdbmService->getSubjects());
        $courses = collect($this->tdbmService->getCourses())
            ->where('semester_id', $currentSemester['semester_id']);
        $studentClasses = collect($this->tdbmService->getStudentClasses())
            ->where('semester_id', $currentSemester['semester_id']);

        $subjectsWithSections = $subjects
            ->filter(function ($subject) use ($courses) {
                return $courses->where('subject_id', $subject['subject_id'])->isNotEmpty();
            })
            ->map(function ($subject) use ($courses, $studentClasses) {
                $course = $courses->where('subject_id', $subject['subject_id'])->first();
                $sections = $studentClasses->where('course_id', $course['course_id'])
                    ->pluck('section_num')
                    ->unique()
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

        // Get requests
        $requests = CourseTas::with([
            'student',
            'course.subjects',
            'courseTaClasses.requests' => function ($query) {
                $query->latest();
            }
        ])
            ->where('student_id', $student->id)
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

            // ตรวจสอบว่านักศึกษามีอยู่จริง
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->first();

            if (!$student) {
                throw new \Exception("ไม่พบข้อมูลนักศึกษารหัส {$studentId}");
            }

            // เช็ค current semester
            $currentSemester = collect($this->tdbmService->getSemesters())
                ->filter(function ($semester) {
                    $startDate = \Carbon\Carbon::parse($semester['start_date']);
                    $endDate = \Carbon\Carbon::parse($semester['end_date']);
                    return now()->between($startDate, $endDate);
                })->first();

            if (!$currentSemester) {
                throw new \Exception('ไม่พบข้อมูลภาคการศึกษาปัจจุบัน');
            }

            // Get course from TDBM API
            $tdbmCourse = collect($this->tdbmService->getCourses())
                ->where('semester_id', $currentSemester['semester_id'])
                ->where('subject_id', $request->subject_id)
                ->first();

            if (!$tdbmCourse) {
                throw new \Exception('ไม่พบข้อมูลรายวิชาที่เลือกใน TDBM');
            }

            // Get subject information from TDBM
            $subjects = collect($this->tdbmService->getSubjects());
            $tdbmSubject = $subjects->where('subject_id', $request->subject_id)->first();

            if (!$tdbmSubject) {
                throw new \Exception('ไม่พบข้อมูลรายวิชาใน TDBM');
            }

            // Get cur_id from course
            $curId = $tdbmCourse['cur_id'] ?? 1;

            // Ensure subject exists in local database
            $localSubject = DB::table('subjects')
                ->where('subject_id', $request->subject_id)
                ->first();

            if (!$localSubject) {
                try {
                    // Create subject in local database
                    DB::table('subjects')->insert([
                        'subject_id' => $tdbmSubject['subject_id'],
                        'name_th' => $tdbmSubject['name_th'] ?? '',
                        'name_en' => $tdbmSubject['name_en'] ?? '',
                        'credit' => $tdbmSubject['credit'] ?? 3,
                        'cur_id' => $curId,
                        'status' => 'w',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Subject creation error:', [
                        'error' => $e->getMessage(),
                        'subject_data' => $tdbmSubject,
                        'cur_id' => $curId
                    ]);
                    throw new \Exception('ไม่สามารถสร้างข้อมูลรายวิชาในฐานข้อมูลได้: ' . $e->getMessage());
                }
            }

            // Check if course exists locally
            $localCourse = DB::table('courses')
                ->where('course_id', $tdbmCourse['course_id'])
                ->first();

            if (!$localCourse) {
                try {
                    $ownerTeacherId = $tdbmCourse['owner_teacher_id'] ?? 1;

                    DB::table('courses')->insert([
                        'course_id' => $tdbmCourse['course_id'],
                        'status' => 'w',
                        'semester_id' => $currentSemester['semester_id'],
                        'subject_id' => $request->subject_id,
                        'owner_teacher_id' => $ownerTeacherId,
                        'cur_id' => $curId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $localCourse = DB::table('courses')
                        ->where('course_id', $tdbmCourse['course_id'])
                        ->first();
                } catch (\Exception $e) {
                    Log::error('Course creation error:', [
                        'error' => $e->getMessage(),
                        'course_data' => [
                            'course_id' => $tdbmCourse['course_id'],
                            'semester_id' => $currentSemester['semester_id'],
                            'subject_id' => $request->subject_id,
                            'owner_teacher_id' => $ownerTeacherId ?? 'not set',
                            'cur_id' => $curId
                        ],
                        'tdbm_course' => $tdbmCourse
                    ]);
                    throw new \Exception('ไม่สามารถสร้างข้อมูลรายวิชาในฐานข้อมูลได้: ' . $e->getMessage());
                }
            }

            // Find existing CourseTa record
            $existingCourseTa = CourseTas::where('student_id', $student->id)
                ->first();

            if ($existingCourseTa) {
                // Get classes for the new sections
                $studentClasses = collect($this->tdbmService->getStudentClasses())
                    ->where('course_id', $tdbmCourse['course_id'])
                    ->whereIn('section_num', $request->sections)
                    ->values();

                if ($studentClasses->isEmpty()) {
                    throw new \Exception('ไม่พบข้อมูลกลุ่มเรียนที่เลือก');
                }

                // Delete existing CourseTaClasses and their requests
                foreach ($existingCourseTa->courseTaClasses as $taClass) {
                    $taClass->requests()->delete();
                }
                $existingCourseTa->courseTaClasses()->delete();

                // Update course_id in existing CourseTa
                $existingCourseTa->course_id = $localCourse->course_id;
                $existingCourseTa->save();

                // Create new CourseTaClasses and Requests for each section
                foreach ($studentClasses as $class) {
                    $localClass = DB::table('classes')
                        ->where('class_id', $class['class_id'])
                        ->first();

                    if (!$localClass) {
                        // Get teacher and major information
                        $teacherId = $class['teacher_id'] ?? $tdbmCourse['owner_teacher_id'] ?? 1;
                        $majorId = $class['major_id'] ?? $tdbmCourse['major_id'] ?? null;

                        DB::table('classes')->insert([
                            'class_id' => $class['class_id'],
                            'course_id' => $localCourse->course_id,
                            'section_num' => $class['section_num'],
                            'title' => 'Section ' . $class['section_num'],
                            'open_num' => $class['open_num'] ?? 30,
                            'enrolled_num' => 0,
                            'available_num' => $class['open_num'] ?? 30,
                            'teacher_id' => $teacherId,
                            'semester_id' => $currentSemester['semester_id'],
                            'major_id' => $majorId,
                            'status' => 'w',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    // Create new CourseTaClass
                    $taClass = new CourseTaClasses([
                        'class_id' => $class['class_id'],
                        'course_ta_id' => $existingCourseTa->id
                    ]);
                    $taClass->save();

                    // Create new Request
                    $newRequest = new Requests([
                        'course_ta_class_id' => $taClass->id,
                        'status' => 'W',
                        'approved_at' => null,
                        'comment' => null
                    ]);
                    $newRequest->save();
                }

                DB::commit();
                Toastr()->success('อัพเดตคำร้องสำเร็จ', 'สำเร็จ!');
                return redirect()->back();
            } else {
                throw new \Exception('ไม่พบคำขอที่ต้องการอัพเดต');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error:', [
                'message' => $e->getMessage(),
                'student_id' => $studentId,
                'subject_id' => $request->subject_id,
                'sections' => $request->sections
            ]);

            Toastr()->error('เกิดข้อผิดพลาดในการอัพเดตคำขอ: ' . $e->getMessage(), 'ผิดพลาด!');
            return redirect()->back();
        }
    }

    public function destroy($studentId)
    {
        try {
            DB::beginTransaction();

            // หา student id จากตาราง students
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->first();

            if (!$student) {
                throw new \Exception('ไม่พบข้อมูลนักศึกษา');
            }

            // หา CourseTa record ของนักศึกษา
            $courseTa = CourseTas::with([
                'courseTaClasses.requests' => function ($query) {
                    $query->latest();
                }
            ])
                ->where('student_id', $student->id)
                ->first();

            if (!$courseTa) {
                throw new \Exception('ไม่พบคำร้องที่ต้องการลบ');
            }

            // ตรวจสอบสถานะคำร้องล่าสุด
            $latestRequest = $courseTa->courseTaClasses
                ->flatMap(function ($taClass) {
                    return $taClass->requests;
                })
                ->sortByDesc('created_at')
                ->first();

            if (!$latestRequest || $latestRequest->status !== 'W') {
                throw new \Exception('ไม่สามารถลบคำร้องที่ดำเนินการแล้วได้');
            }

            // ลบ Requests ที่เกี่ยวข้อง
            foreach ($courseTa->courseTaClasses as $taClass) {
                $taClass->requests()->delete();
            }

            // ลบ CourseTaClasses
            $courseTa->courseTaClasses()->delete();

            // ลบ CourseTa
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

            Toastr()->error('เกิดข้อผิดพลาดในการลบคำร้อง: ' . $e->getMessage(), 'ผิดพลาด!');
            return redirect()->back();
        }
    }
}
