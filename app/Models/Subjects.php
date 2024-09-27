<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subjects extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    protected $keyType = 'string'; // กำหนดให้ primary key เป็น string
    public $incrementing = false;  // ปิดการใช้งาน auto-increment

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject_id',
        'name_th',
        'name_en',
        'credits',
        'weight',
        'detail',
        'cur_id',
        'status',
    ];

    /**
     * Get the curriculum that owns the subject.
     */
    public function curriculums()
    {
        return $this->belongsTo(Curriculums::class);
    }

    public function curriculums_subjects()
    {
        return $this->hasMany(CurriculumsSubjects::class);
    }

    public function students()
    {
        return $this->hasMany(Students::class);
    }

    public function courses()
    {
        return $this->hasMany(Courses::class, 'subject_id', 'subject_id');
    }
}
