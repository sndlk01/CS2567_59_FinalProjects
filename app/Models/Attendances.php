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
        'student_id',
        'approve_user_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function teaching()
    {
        return $this->belongsTo(Teaching::class, 'teaching_id');
    }

    public function student()
    {
        return $this->hasOne(Students::class);
    }
}
