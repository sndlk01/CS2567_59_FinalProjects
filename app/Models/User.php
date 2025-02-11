<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefix',
        'name',
        'card_id',
        'phone',
        'student_id',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected function type(): Attribute
    {
        return new Attribute(
            get: fn($value) => ["user", "admin", "teacher"][$value],
        );
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function students()
    {
        return $this->hasOne(Students::class);
    }

    public function student()
    {
        return $this->hasOne(Students::class);
    }

    public function attendences()
    {
        return $this->hasMany(Attendances::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teachers::class, 'user_id');
    }
}
