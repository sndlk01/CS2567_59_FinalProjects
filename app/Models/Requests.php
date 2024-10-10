<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;
    protected $table = 'requests';
    protected $fillable = [
        'student_id',
        'course_id',
        'approved_at',
        'comment',
        'status',
        // 'approved_at' => 'datetime',
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    public function courseTas()
{
    return $this->belongsTo(CourseTas::class, 'course_id', 'course_id');
}
    
}
