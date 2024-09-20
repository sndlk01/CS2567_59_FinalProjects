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
            [
                'start_time' => '2024-08-07 09:00:00',
                'end_time' => '2024-08-07 12:00:00',
                'duration' => 3,
                'class_type_id' => 1,
                'status' => 'A', 
                'classes_id' => 1, 
                'teachers_id' => 1, 
            ],
            [
                'start_time' => '2024-09-07 13:00:00',
                'end_time' => '2024-09-07 15:00:00',
                'duration' => 2,
                'class_type_id' => 2,
                'status' => 'A', 
                'classes_id' => 1, 
                'teachers_id' => 1, 
            ],
        ];

        foreach ($teaching as $key => $value) {
            Teaching::create($value);
        }
    }
}
