<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



class TaController extends Controller
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


    /// TA ROLE
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function request()
    {
        $subjects = Subjects::all();
        $subjectsWithSections = [];

        foreach ($subjects as $subject) {
            $course = Courses::where('subject_id', $subject->subject_id)->first();
            if ($course) {
                $sections = Classes::where('course_id', $course->id)->pluck('section_num')->toArray();
                $subjectsWithSections[] = [
                    'subject' => $subject,
                    'sections' => $sections
                ];
            }
        }

        return view('layouts.ta.request', compact('subjectsWithSections'));
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
        $user = Auth::user();

        // Validate the incoming request data
        $request->validate([
            'applications' => 'required|array|min:1|max:3',
            'applications.*.subject_id' => 'required|exists:subjects,subject_id',
            'applications.*.sections' => 'required|array|min:1',
            'applications.*.sections.*' => 'required|numeric',
        ]);

        // Create or update the student record
        $student = Students::updateOrCreate(
            ['user_id' => $user->id],
            [
                'prefix' => $user->prefix,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'student_id' => $user->student_id,
                'email' => $user->email,
                'card_id' => $user->card_id,
                'phone' => $user->phone,
            ]
        );

        $applications = $request->input('applications');

        $currentCourseCount = CourseTas::where('student_id', $student->id)->count();
        if ($currentCourseCount + count($applications) > 3) {
            return redirect()->back()->with('error', 'คุณไม่สามารถสมัครเป็นผู้ช่วยสอนได้เกิน 3 วิชา');
        }

        foreach ($applications as $application) {
            $subjectId = $application['subject_id'];
            $sectionNums = $application['sections'];

            // Find the course with the matching subject_id
            $course = Courses::where('subject_id', $subjectId)->first();

            if ($course) {
                // Check if the user has already applied for this course
                $existingTA = CourseTas::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->first();

                if ($existingTA) {
                    return redirect()->back()->with('error', 'คุณได้สมัครเป็นผู้ช่วยสอนในวิชา ' . $subjectId . ' แล้ว');
                }

                // สร้าง course_ta
                $courseTA = CourseTas::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ]);

                foreach ($sectionNums as $sectionNum) {
                    // ตรวจสอบว่า section_num มีใน classes หรือไม่
                    $class = Classes::where('section_num', $sectionNum)
                        ->where('course_id', $course->id)
                        ->first();

                    if ($class) {
                        // สร้าง course_ta_classes
                        $courseTaClass = CourseTaClasses::create([
                            'class_id' => $class->id,
                            'course_ta_id' => $courseTA->id,
                        ]);

                        // Save to requests table
                        Requests::create([
                            'course_ta_class_id' => $courseTaClass->id,
                            'status' => 'W', // Pending status
                            'comment' => null,
                            'approved_at' => null,
                        ]);
                    } else {
                        return redirect()->back()->with('error', 'ไม่พบเซคชัน ' . $sectionNum . ' สำหรับวิชา ' . $subjectId);
                    }
                }
            } else {
                return redirect()->back()->with('error', 'ไม่พบรายวิชา ' . $subjectId . ' ในระบบ');
            }
        }

        return redirect()->route('layout.ta.request')->with('success', 'สมัครเป็นผู้ช่วยสอนสำเร็จ');
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
            // ถ้าไม่มีข้อมูลนักศึกษา ให้ redirect ไปที่หน้าหลักหรือหน้าแจ้งเตือน
            return redirect()->route('layout.ta.request')->with('error', 'ไม่พบข้อมูลนักศึกษา');
        }
    

        // ดึงข้อมูลจาก course_ta_classes ผ่าน course_tas แล้วไปดึงข้อมูล courses
        $courseTaClasses = CourseTaClasses::with([
            'courseTa.course.subjects',   // ดึงข้อมูลวิชา
            'courseTa.course.semesters',  // ดึงข้อมูลปีการศึกษา และเทอม
            'courseTa.course.teachers',   // ดึงข้อมูลอาจารย์
            'courseTa.course.curriculums', // ดึงข้อมูลหลักสูตร
            'class'                        // ดึงข้อมูล section_num จากตาราง classes
        ])->whereHas('courseTa', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })->get();

     

        // วนลูปผ่านข้อมูล courseTaClasses เพื่อประมวลผล major name_th
        foreach ($courseTaClasses as $courseTaClass) {
            // if (isset($courseTaClass->courseTa->course->major->name_th)) {
            //     // ใช้ Str::after() เพื่อดึงเฉพาะคำว่า "ภาคปกติ"
            //     $courseTaClass->courseTa->course->major->name_th = trim(Str::after($courseTaClass->courseTa->course->major->name_th, ' '));
            // }
            if (isset($courseTaClass->courseTa->course->curriculums->name_th)) {
                // ใช้ Str::before() เพื่อดึงเฉพาะชื่อสาขา
                $courseTaClass->courseTa->course->curriculums->name_th = trim(Str::before($courseTaClass->courseTa->course->curriculums->name_th, ' '));
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
}
