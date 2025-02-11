<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRequest extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_id',
        'status',
        'payment_type'
    ];

    protected $casts = [
        'status' => 'string',
        'payment_type' => 'string'
    ];

    // ความสัมพันธ์กับอาจารย์
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    // ความสัมพันธ์กับรายวิชา
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'course_id');
    }

    // ความสัมพันธ์กับรายละเอียดคำร้อง
    public function details()
    {
        return $this->hasMany(TeacherRequestsDetail::class);
    }
}