<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teaching;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TeachingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teaching = [
            // CS
            // CP352001 Data Structure
            // มิถุ
            [
                'start_time' => '2024-06-24 08:30:00',
                'end_time' => '2024-06-24 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-06-24 10:30:00',
                'end_time' => '2024-06-24 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            // กรก
            [
                'start_time' => '2024-07-01 08:30:00',
                'end_time' => '2024-07-01 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-01 10:30:00',
                'end_time' => '2024-07-01 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-08 08:30:00',
                'end_time' => '2024-07-08 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-08 10:30:00',
                'end_time' => '2024-07-08 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-15 08:30:00',
                'end_time' => '2024-07-15 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-15 10:30:00',
                'end_time' => '2024-07-15 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-22 08:30:00',
                'end_time' => '2024-07-22 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-22 10:30:00',
                'end_time' => '2024-07-22 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-29 08:30:00',
                'end_time' => '2024-07-29 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-07-29 10:30:00',
                'end_time' => '2024-07-29 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            // สิง
            [
                'start_time' => '2024-08-05 08:30:00',
                'end_time' => '2024-08-05 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-05 10:30:00',
                'end_time' => '2024-08-05 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-12 08:30:00',
                'end_time' => '2024-08-12 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-12 10:30:00',
                'end_time' => '2024-08-12 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-19 08:30:00',
                'end_time' => '2024-08-19 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-19 10:30:00',
                'end_time' => '2024-08-19 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-26 08:30:00',
                'end_time' => '2024-08-26 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-08-26 10:30:00',
                'end_time' => '2024-08-26 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            // กัน
            [
                'start_time' => '2024-09-02 08:30:00',
                'end_time' => '2024-09-02 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-02 10:30:00',
                'end_time' => '2024-09-02 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-09 08:30:00',
                'end_time' => '2024-09-09 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-09 10:30:00',
                'end_time' => '2024-09-09 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-16 08:30:00',
                'end_time' => '2024-09-16 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-16 10:30:00',
                'end_time' => '2024-09-16 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-23 08:30:00',
                'end_time' => '2024-09-23 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-23 10:30:00',
                'end_time' => '2024-09-23 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-30 08:30:00',
                'end_time' => '2024-09-30 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-09-30 10:30:00',
                'end_time' => '2024-09-30 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            // ตุลา
            [
                'start_time' => '2024-10-07 08:30:00',
                'end_time' => '2024-10-07 10:30:00',
                'duration' => 120,
                'class_type' => 'C',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
            [
                'start_time' => '2024-10-07 10:30:00',
                'end_time' => '2024-10-07 12:30:00',
                'duration' => 120,
                'class_type' => 'L',
                'status' => 'A',
                'class_id' => 1,
                'teacher_id' => 24,
            ],
        ];

        foreach ($teaching as $key => $value) {
            Teaching::create($value);
        }
    }
}
