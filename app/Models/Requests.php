<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;
    protected $table = 'requests';
    protected $fillable = [
        'course_ta_class_id',
        'approved_at',
        'comment',
        'status',
    ];

    public function courseTaClass()
    {
        return $this->belongsTo(CourseTaClasses::class, 'course_ta_class_id');
    }

}
