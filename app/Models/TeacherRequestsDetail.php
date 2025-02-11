<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRequestsDetail extends Model
{
    protected $table = 'teacher_requests_detail';

    protected $fillable = [
        'teacher_request_id',
        'group_number',
        'undergrad_count',
        'graduate_count'
    ];

    // ความสัมพันธ์กับคำร้องหลัก
    public function teacherRequest()
    {
        return $this->belongsTo(TeacherRequest::class);
    }

    // ความสัมพันธ์กับรายชื่อนักศึกษา TA
    public function students()
    {
        return $this->hasMany(TeacherRequestStudent::class, 'teacher_requests_detail_id');
    }

    // app/Models/Course.php
public function teacherRequests()
{
    return $this->hasManyThrough(
        TeacherRequest::class,
        TeacherRequestsDetail::class,
        'course_id',  // Foreign key on teacher_requests_detail table
        'id',         // Foreign key on teacher_requests table
        'course_id',  // Local key on courses table
        'teacher_request_id'  // Local key on teacher_requests_detail table
    );
}
}