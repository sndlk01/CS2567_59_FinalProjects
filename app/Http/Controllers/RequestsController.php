<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Requests;
// use Log;


class RequestsController extends Controller
{
    public function showTARequests()
    {
        $user = Auth::user();
        $student = $user->student;
    
        if (!$student) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลนักศึกษาสำหรับผู้ใช้นี้');
        }
    
        // Build the query
        $query = Requests::with([
            'courseTaClass.courseTa.student',
            'courseTaClass.class.course.subjects'
        ])
        ->whereHas('courseTaClass.courseTa', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
        ->latest();
    
        // Debug: Log the SQL query
        Log::info('SQL Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    
        // Execute the query
        $requests = $query->get();
    
        // Debug: Log the result
        Log::info('Requests:', [
            'count' => $requests->count(),
            'data' => $requests->toArray()
        ]);
    
        return view('layouts.ta.statusRequest', compact('requests'));
    }


}
