<?php

namespace App\Http\Controllers;

use App\Models\CourseTas;
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

        return view('layouts.ta.statusRequest', compact('requests'));
    }
}
