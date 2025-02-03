<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRequest extends Model
{
   protected $fillable = [
       'class_id',
       'teacher_id',
       'lab_allowed',
       'lecture_allowed',
       'status'
   ];

   protected $casts = [
       'lab_allowed' => 'boolean', 
       'lecture_allowed' => 'boolean'
   ];

   public function class(): BelongsTo 
   {
       return $this->belongsTo(Classes::class, 'class_id', 'class_id');
   }

   public function teacher(): BelongsTo
   {
       return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
   }
}
