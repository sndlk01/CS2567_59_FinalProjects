<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculums extends Model
{
    use HasFactory;

    /**

     *
     * @var array
     */
    protected $table = 'curriculums';
    protected $primaryKey = 'cur_id';  // เปลี่ยนจาก id เป็น cur_id

    protected $fillable = [
        'cur_id',
        'name_th',
        'name_en',
        'head_teacher_id',
        'curr_type'
    ];

    public $incrementing = false;

    /**
     * Get the user that owns the employee.
     */
    public function teachers()
    {
        return $this->belongsTo(Teachers::class);
    }

    public function major()
    {
        return $this->belongsToMany(Major::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subjects::class);
    }

    public function curriculums_subjects()
    {
        return $this->belongsTo(CurriculumsSubjects::class);
    }

    public function courses()
    {
        return $this->hasMany(Courses::class, 'cur_id', 'cur_id');
    }
}
