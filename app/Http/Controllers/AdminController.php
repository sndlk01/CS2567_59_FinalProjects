<?php

namespace App\Http\Controllers;
use App\Models\Announce;
use App\Models\Courses;
use App\Models\Students;
use App\Models\Disbursements;
use Illuminate\Support\Facades\Storage;



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
        \Log::info('จำนวนรายวิชาที่มี TA: ' . $coursesWithTAs->count());

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


    public function showTaProfile($student_id) 
    {
        $student = Students::with([
            'disbursements',  // ใช้ชื่อ relationship ที่ถูกต้อง
            'courseTas.course.subjects',
            'courseTas.course.teachers',
            'courseTas.course.semesters',
            'courseTas.courseTaClasses.requests' => function ($query) {
                $query->where('status', 'A')
                    ->whereNotNull('approved_at');
            }
        ])->findOrFail($student_id);
    
        // ดูข้อมูลที่แท้จริงใน attributes
        // dd([
        //     'student_exists' => $student !== null,
        //     'student_id' => $student->id,
        //     'raw_disbursements' => $student->disbursements,
        //     'attributes' => $student->disbursements?->getAttributes(),
        //     'relationship_loaded' => $student->relationLoaded('disbursements')
        // ]);
    
        return view('layouts.admin.detailsById', compact('student'));
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
            \Log::error('Document download error: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการดาวน์โหลดเอกสาร');
        }
    }

    public function detailsTa()
    {
        return view('layouts.admin.detailsTa');
    }


    public function detailsByid()
    {
        return view('layouts.admin.detailsByid');
    }




    /**
     * Display a listing of the resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
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

}
