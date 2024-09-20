<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'course_id',
    ];

    public function teacher()
    {
        return $this->hasOne(Teachers::class);
    }

    public function courses()
    {
        return $this->hasOne(Courses::class);
    }
}
