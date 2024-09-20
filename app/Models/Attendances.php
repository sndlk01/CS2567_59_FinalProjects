<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendances extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'approve_at',
        'note',
        'user_id',
        'teaching_id',
        'student_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function teaching()
    {
        return $this->hasOne(Teaching::class);
    }

    public function student()
    {
        return $this->hasOne(Students::class);
    }
}
