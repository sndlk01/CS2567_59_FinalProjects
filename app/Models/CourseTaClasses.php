<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTaClasses extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'course_ta_id',
    ];

    // Each CourseTaClasses belongs to one class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // Each CourseTaClasses belongs to one CourseTa
    public function courseTa()
    {
        return $this->belongsTo(CourseTas::class, 'course_ta_id');
    }
}
