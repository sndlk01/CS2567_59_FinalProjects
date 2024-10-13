<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    // ตรวจสอบว่า section_num มีอยู่ในตาราง classes หรือไม่
    public function validateSection(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $sectionNum = $request->input('section_num');

        // $sectionExists = DB::table('sections')
        //     ->where('subject_id', $subjectId)
        //     ->where('section_num', $sectionNum)
        //     ->exists();

        // return response()->json(['exists' => $sectionExists]);

        // ตรวจสอบว่ามี section_num ในวิชานี้หรือไม่
        $exists = Classes::whereHas('course', function ($query) use ($subjectId) {
            $query->where('subject_id', $subjectId);
        })->where('section_num', $sectionNum)->exists();

        return response()->json(['exists' => $exists]);
    }
}
