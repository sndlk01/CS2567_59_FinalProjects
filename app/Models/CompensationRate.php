<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompensationRate extends Model
{
    protected $fillable = [
        'teaching_type',
        'class_type',
        'rate_per_hour',
        'status'
    ];

    protected $casts = [
        'rate_per_hour' => 'decimal:2',
    ];

    // ค้นหาอัตราค่าตอบแทนที่เหมาะสม
    public static function getActiveRate($teachingType, $classType)
    {
        return self::where('teaching_type', $teachingType)
            ->where('class_type', $classType)
            ->where('status', 'active')
            ->first();
    }
}