<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRequestStudent extends Model
{
    protected $fillable = [
        'teacher_requests_detail_id',
        'course_ta_id',
        'teaching_hours',
        'prep_hours',
        'grading_hours',
        'other_hours',
        'other_duties',
        'total_hours_per_week'
    ];

    // ความสัมพันธ์กับรายละเอียดคำร้อง
    public function requestDetail()
    {
        return $this->belongsTo(TeacherRequestsDetail::class, 'teacher_requests_detail_id');
    }

    // ความสัมพันธ์กับ CourseTa
    public function courseTa()
    {
        return $this->belongsTo(CourseTas::class, 'course_ta_id');
    }

    // Helper method เพื่อดึงข้อมูลนักศึกษา
    public function getStudent()
    {
        return $this->courseTa->student;
    }

    // Helper method เพื่อดึงข้อมูลรายวิชา
    public function getCourse()
    {
        return $this->courseTa->course;
    }
}