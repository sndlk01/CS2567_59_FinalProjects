<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teachers extends Model
{
    use HasFactory;

    /**

     *
     * @var array
     */
    protected $table = 'teachers';

    protected $primaryKey = 'teacher_id';
    protected $fillable = [
        'prefix',
        'position',
        'degree',
        'name',
        'email',
        'user_id'
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curriculums()
    {
        return $this->hasOne(Curriculums::class);
    }

    public function courses()
    {
        return $this->hasMany(Courses::class, 'owner_teacher_id');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'teacher_id');
    }

    // สร้างความสัมพันธ์กับ Teaching
    public function teachings()
    {
        return $this->hasMany(Teaching::class, 'teacher_id');
    }

    public function course_teacher()
    {
        return $this->belongsTO(CourseTeacher::class);
    }

    public function extra_teaching()
    {
        return $this->hasMany(ExtraTeaching::class);
    }
    public function teacherRequests()
    {
        return $this->hasMany(TeacherRequest::class, 'teacher_id');
    }
}
