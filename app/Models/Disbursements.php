<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursements extends Model
{
    use HasFactory;

    protected $table = 'disbursements';

    protected $fillable = [
        'applicant_type',
        'bookbank_id',
        'bank_name',
        'uploadfile',
        'student_id'
    ];


    public function student()
    {
        return $this->belongsTo(Students::class);
    }
}
