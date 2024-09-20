<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraTeaching extends Model
{
    use HasFactory;

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

    public function teacher()
    {
        return $this->hasOne(Teachers::class);
    }

    public function teaching()
    {
        return $this->hasOne(Teaching::class);
    }

    public function class()
    {
        return $this->hasOne(Classes::class);
    }
}
