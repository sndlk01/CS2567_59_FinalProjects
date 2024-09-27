<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    use HasFactory;

    /**

     *
     * @var array
     */
    protected $table = 'class_type';

    protected $keyType = 'char';

    public $incrementing = false;  // ปิดการใช้งาน auto-increment
    protected $fillable = [
        'title',
    ];

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function teaching()
    {
        return $this->hasMany(Teaching::class);
    }

    public function extra_attendences()
    {
        return $this->hasMany(ExtraAttendances::class);
    }
}