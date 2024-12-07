<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semesters extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'semester_id',
        'year',
        'semesters', // make sure this is included
        'start_date',
        'end_date'
    ];

    public function courses()
    {
        return $this->belongsTo(Courses::class, 'semester_id');
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class);
    }

}