<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTas extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }

    public function requests()
    {
        return $this->hasOne(Requests::class, 'course_tas_id', 'id');
    }

    // ความสัมพันธ์กับ CourseTaClasses
    public function courseTaClasses()
    {
        return $this->hasMany(CourseTaClasses::class, 'course_ta_id', 'id');
    }
}
