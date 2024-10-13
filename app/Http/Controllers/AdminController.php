<?php

namespace App\Http\Controllers;
use App\Models\Announce;
use App\Models\Courses;
use App\Models\course_tas;


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
    //  /**
    //  * Show the application dashboard.
    //  *
    //  * @return \Illuminate\Contracts\Support\Renderable
    //  */
    // public function announce()
    // {
    //     return view('layouts.admin.announce');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function taUsers()
    {

        $coursesWithTAs = Courses::whereHas('course_tas', function($query) {
            $query->whereNotNull('approved_at');
        })
        ->with(['subjects', 'teachers', 'course_tas' => function($query) {
            $query->whereNotNull('approved_at');
        }])
        ->get();

    return view('layouts.admin.taUsers', compact('coursesWithTAs'));

    }
    
    public function showTADetails($courseId)
    {
        $course = Courses::with(['subjects', 'teachers', 'course_tas.student'])
            ->findOrFail($courseId);

        return view('admin.course_ta_details', compact('course'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function detailsTa()
    {
        return view('layouts.admin.detailsTa');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
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
        return view('layouts.admin.index',compact('announces'))
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
            ->with('success','announce created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announce $announce)
    {
        return view('layouts.admin.show',compact('announce'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announce $announce)
    {
        return view('layouts.admin.edit',compact('announce'));
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
            ->with('success','announce updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announce $announce)
    {
        $announce->delete();
        return redirect()
            ->route('announces.index')
            ->with('success','announce deleted successfully');
    }

}
