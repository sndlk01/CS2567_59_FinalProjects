<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'student_id',
        'prefix',
        'fname',
        'lname',
        'card_id',
        'phone',
        'email',
        'type_ta',
        // 'dis_id',
        'user_id',
        'subject_id',
    ];


    public function disbursements()
    {
        return $this->hasOne(Disbursements::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subjects::class);
    }

    public function student()
    {
        return $this->hasMany(Students::class);
    }

    public function course_tas()
    {
        return $this->hasMany(Students::class);
    }
}
