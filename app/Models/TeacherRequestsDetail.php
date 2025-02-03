<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TeacherRequestsDetail extends Model
{
   protected $fillable = [
       'teacher_request_id',
       'student_code',
       'name',
       'phone',
       'education_level',
       'total_hours_per_week',
       'lecture_hours',
       'lab_hours',
       'payment_type'
   ];

   public function teacherRequest(): BelongsTo
   {
       return $this->belongsTo(TeacherRequest::class);
   }
}