<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendances extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'approve_status',
        'approve_at',
        'approve_note',
        'note',
        'user_id',
        'teaching_id',
        'extra_teaching_id',
        'is_extra',
        'student_id',
        'approve_user_id',
    ];

    // public function user()
    // {
    //     return $this->hasOne(User::class);
    // }

    // public function teaching()
    // {
    //     return $this->belongsTo(Teaching::class, 'teaching_id');
    // }

    // public function student()
    // {
    //     return $this->hasOne(Students::class);
    // }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teaching()
    {
        return $this->belongsTo(Teaching::class, 'teaching_id', 'teaching_id');
    }

    public function extraTeaching()
    {
        return $this->belongsTo(ExtraTeaching::class, 'extra_teaching_id', 'extra_class_id');
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id', 'id');
    }
}
