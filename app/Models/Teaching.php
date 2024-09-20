<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teaching extends Model
{
    use HasFactory;

    protected $table = 'teaching';

    protected $fillable = [
        'start_time',
        'end_time',
        'duration',
        'class_type_id',
        'status',
        'classes_id',
        'teachers_id',
    ];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class);
    }
    
    public function teachers()
    {
        return $this->belongsTo(Teachers::class);
    }

    public function attendences()
    {
        return $this->hasMany(Attendances::class);
    }

    public function extra_teaching()
    {
        return $this->hasMany(ExtraTeaching::class);
    }
}
