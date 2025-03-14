<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announce extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'title',
        'description',
        'semester_id',
        'is_active'
    ];

    public function semester()
    {
        return $this->belongsTo(Semesters::class, 'semester_id', 'semester_id');
    }

    // Scope เพื่อกรองประกาศตามเทอมปัจจุบัน
    public function scopeCurrentSemester($query)
    {
        $currentSemesterId = session('user_active_semester_id');
        return $query->where('semester_id', $currentSemesterId)
            ->where('is_active', true);
    }
}
