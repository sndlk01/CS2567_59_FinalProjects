<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teaching extends Model
{
    use HasFactory;

    protected $table = 'teaching';
    protected $primaryKey = 'teaching_id';

    public $incrementing = false;

    protected $fillable = [
        'teaching_id',
        'start_time',
        'end_time',
        'duration',
        'class_type',
        'status',
        'class_id',
        'teacher_id'
    ];

    protected $dates = [
        'start_time',
        'end_time',
        'created_at',
        'updated_at'
    ];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    // สร้างความสัมพันธ์ไปยัง Classes
    // public function class()
    // {
    //     return $this->belongsTo(Classes::class, 'class_id');
    // }

    // สร้างความสัมพันธ์ไปยัง Teachers
    // public function teacher()
    // {
    //     return $this->belongsTo(Teachers::class, 'teacher_id');
    // }

    // public function attendance()
    // {
    //     return $this->hasOne(Attendances::class, 'teaching_id', 'teaching_id');
    // }

    public function extra_teaching()
    {
        return $this->hasMany(ExtraTeaching::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendances::class, 'teaching_id', 'teaching_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teachers::class, 'teacher_id', 'teacher_id');
    }
}
