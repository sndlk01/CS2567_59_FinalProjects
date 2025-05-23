<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraAttendances extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail',
        'start_work',
        'duration',
        'class_type',
        'student_id',
        'class_id',
        'approve_status',
        'approve_note',
        'approve_at',
        'approve_user_id',
    ];

    public function student()
    {
        return $this->hasOne(Students::class);
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id');
    }
    
}
