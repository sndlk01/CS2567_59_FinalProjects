<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseTas;


class CourseTasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course_tas = [
            [
                'student_id' => 1, 
                'course_id' => 1,
            ],
            [
                'student_id' =>  1, 
                'course_id' => 1, 
            ],
        ];

        foreach ($course_tas as $key => $value) {
            CourseTas::create($value);
        }
    }
}
