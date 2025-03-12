<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraTeaching extends Model
{
    use HasFactory;

    protected $primaryKey = 'extra_class_id';

    public $incrementing = false;

    protected $fillable = [
        'title',
        'detail',
        'opt_status',
        'status',
        'class_date',
        'start_time',
        'end_time',
        'duration',
        'teacher_id',
        'holiday_id',
        'teaching_id',
        'class_id',
    ];

    public function attendance()
    {
        return $this->hasOne(Attendances::class, 'extra_teaching_id', 'extra_class_id');
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
