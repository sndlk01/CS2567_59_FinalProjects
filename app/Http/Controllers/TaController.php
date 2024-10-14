<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Courses;
use App\Models\CourseTaClasses;
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
        return view('layouts.ta.request', compact('subjects'));
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
            'subject_id' => 'required|array|min:1|max:3',
            'subject_id*' => 'exists:subjects,subject_id',
            'section_num' => 'required|numeric',
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
    
        $subjectIds = $request->input('subject_id');
        $sectionNum = $request->input('section_num');
    
        $currentCourseCount = CourseTas::where('student_id', $student->id)->count();
        if ($currentCourseCount + count($subjectIds) > 3) {
            return redirect()->back()->with('error', 'คุณไม่สามารถสมัครเป็นผู้ช่วยสอนได้เกิน 3 วิชา');
        }
    
        foreach ($subjectIds as $subjectId) {
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
    
                // ตรวจสอบว่า section_num มีใน classes หรือไม่
                $class = Classes::where('section_num', $sectionNum)
                    ->where('course_id', $course->id)
                    ->first();
    
                if ($class) {
                    // ตรวจสอบว่า course_id ของ class และ course_ta ตรงกันหรือไม่
                    if ($class->course_id === $courseTA->course_id) {
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
                        return redirect()->back()->with('error', 'Course ID ไม่ตรงกันระหว่าง classes และ course_ta');
                    }
                } else {
                    return redirect()->back()->with('error', 'ไม่พบเซคชัน ' . $sectionNum . ' สำหรับวิชา ' . $subjectId);
                }
            } else {
                return redirect()->back()->with('error', 'ไม่พบรายวิชา ' . $subjectId . ' ในระบบ');
            }
        }
    
        return redirect()->route('layout.ta.request')->with('success', 'สมัครเป็นผู้ช่วยสอนสำเร็จ');
    }


    public function showCourseTas()
    {
        $user = Auth::user();
        $student = Students::where('user_id', $user->id)->first();

        // ดึงข้อมูลจาก course_tas พร้อมกับข้อมูลจากตารางที่เกี่ยวข้อง
        $courseTas = CourseTas::with([
            'course.subjects',         // ดึงข้อมูล subject_id และ name_en
            'course.semesters',        // ดึงข้อมูลปีการศึกษา และเทอม
            'course.teachers',         // ดึงข้อมูลอาจารย์
            'course.curriculums',      // ดึงข้อมูลหลักสูตร
            'course.major'            // ดึงข้อมูลสาขา
        ])->where('student_id', $student->id)->get();

        // วนลูปผ่านข้อมูล courseTas เพื่อประมวลผล major name_th
        foreach ($courseTas as $courseTa) {
            if (isset($courseTa->course->major->name_th)) {
                // ใช้ Str::after() เพื่อดึงเฉพาะคำว่า "ภาคปกติ"
                $courseTa->course->major->name_th = trim(Str::after($courseTa->course->major->name_th, ' '));
            }
            if (isset($courseTa->course->curriculums->name_th)) {
                // ใช้ Str::before() เพื่อดึงเฉพาะชื่อสาขา
                $courseTa->course->curriculums->name_th = trim(Str::before($courseTa->course->curriculums->name_th, ' '));
            }
        }
        return view('layouts.ta.taSubject', compact('courseTas'));
    }

    public function showSubjectDetail($id)
    {
        $user = Auth::user();
        $student = Students::where('user_id', $user->id)->first();

        // ดึงข้อมูลเฉพาะที่มี course_ta id ตรงกับ $id ที่ส่งมา
        $courseTa = CourseTas::with([
            'course.subjects',         // ดึงข้อมูล subject_id และ name_en
            'course.semesters',        // ดึงข้อมูลปีการศึกษา และเทอม
            'course.teachers',         // ดึงข้อมูลอาจารย์
            'course.curriculums',      // ดึงข้อมูลหลักสูตร
            'course.major'            // ดึงข้อมูลสาขา
        ])->where('id', $id)  // กรองด้วย id ของ course_ta
            ->where('student_id', $student->id)  // กรองด้วย student_id ด้วยเพื่อความปลอดภัย
            ->first(); // ใช้ first() แทน get() เพื่อดึงข้อมูลเฉพาะ 1 รายการ

        // ตรวจสอบว่าข้อมูลถูกต้องหรือไม่
        if (!$courseTa) {
            abort(404, 'ไม่พบข้อมูล CourseTa ที่ระบุ');
        }

        // วนลูปผ่านข้อมูล courseTas เพื่อประมวลผล major name_th
        foreach ($courseTa as $courseta) {
            if (isset($courseta->course->major->name_th)) {
                // ใช้ Str::after() เพื่อดึงเฉพาะคำว่า "ภาคปกติ"
                $courseta->course->major->name_th = trim(Str::after($courseta->course->major->name_th, ' '));
            }
        }
        return view('layouts.ta.attendances', compact('courseTa', 'student'));
    }
}
