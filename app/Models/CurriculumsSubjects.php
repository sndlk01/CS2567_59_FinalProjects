<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumsSubjects extends Model
{
    use HasFactory;

    protected $table = 'curriculums_subjects';
    protected $fillable = [
        'cur_id', 
        'subjects_id'
    ];

    
    public function curriculums()
    {
        return $this->belongsTo(Curriculums::class);
    }

    public function subjects()  
    {
        return $this->belongsTo(Subjects::class);
    }

}
