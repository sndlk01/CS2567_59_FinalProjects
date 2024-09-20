<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;


class ClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            [
                'section_num' => 1,
                'title' => 'กลุ่ม 1',
                'class_type_id' => 1, 
                'open_num' => 30,
                'enrolled_num' => 25,
                'available_num' => 5,
                'teachers_id' => 1, 
                'courses_id' => 1, 
                'semesters_id' => 1, 
                'major_id' => 1,

            ],
            [
                'section_num' => 2,
                'title' => 'กลุ่ม 2',
                'class_type_id' => 2, 
                'open_num' => 25,
                'enrolled_num' => 20,
                'available_num' => 5,
                'teachers_id' => 1, 
                'courses_id' => 1, 
                'semesters_id' => 1, 
                'major_id' => 1,

            ],
        ];

        foreach ($classes as $key => $value) {
            Classes::create($value);
        }
    }
}
