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

    public function showTARequests()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลนักศึกษาสำหรับผู้ใช้นี้');
        }

        $requests = Requests::with(['courseTas.course.subjects', 'courseTas.student'])
            ->whereHas('courseTas', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->latest()
            ->get();

        // \Log::info('Number of requests found: ' . $requests->count());

        return view('layouts.ta.statusRequest', compact('requests'));
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
            'subject_id' => 'required|array|min:1|max:3', // ตรวจสอบว่าเลือกอย่างน้อย 1 วิชา และเลือกไม่เกิน 3 วิชา
            'subject_id*' => 'exists|exists:subjects,subject_id', // ตรวจสอบว่าแต่ละวิชาที่เลือกมีอยู่ในฐานข้อมูล
            'sections' => 'required|array', // ตรวจสอบว่ามีการเลือก section สำหรับแต่ละวิชา
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

        // รับ subject_id ที่เลือกจากฟอร์ม
        $subjectIds = $request->input('subject_id');
        $sections = $request->input('sections'); // ตัวอย่างข้อมูลที่ส่งมาจากฟอร์ม: ['course_1' => [1, 2], 'course_2' => [1]]
        dd($sections); // ตรวจสอบค่า sections ที่ได้รับมา

        // ตรวจสอบว่านักศึกษานี้ได้สมัครเป็นผู้ช่วยสอนครบ 3 วิชาหรือยัง
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

                // บันทึกข้อมูลลงใน course_tas
                $courseTA = CourseTas::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ]);
                // Save to requests table
                Requests::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'W', // Assuming 'P' means pending
                ]);

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
