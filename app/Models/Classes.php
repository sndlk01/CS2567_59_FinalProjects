<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'section_num',
        'title',
        'class_type_id',
        'open_num',
        'enrolled_num',
        'available_num',
        'teachers_id',
        'courses_id',
        'semesters_id',
        'major_id',
        'status'
    ];


    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }


    public function teachers()
    {
        return $this->belongsTo(Teachers::class, 'teacher_id');
    }

    // ความสัมพันธ์กับ Course
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }


    public function semesters()
    {
        return $this->belongsTo(Semesters::class, 'semester_id');
    }


    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    // สร้างความสัมพันธ์กับ Teaching
    public function teachings()
    {
        return $this->hasMany(Teaching::class, 'class_id');
    }

    public function extra_teaching()
    {
        return $this->hasMany(ExtraTeaching::class);
    }

    public function courseTaClasses()
    {
        return $this->hasMany(CourseTaClasses::class, 'class_id', 'id');
    }
}
