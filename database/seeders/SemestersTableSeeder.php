<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semesters;

class SemestersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            [
                'id' => 25661,
                'year' => 2566,
                'semesters' => 1,
                'start_date' => '2023-06-19',
                'end_date' => '2023-10-15',
            ],
            [
                'id' => 25662,
                'year' => 2566,
                'semesters' => 2,
                'start_date' => '2023-11-13',
                'end_date' => '2024-03-10',
            ],
            [
                'id' => 25671,
                'year' => 2567,
                'semesters' => 1,
                'start_date' => '2024-06-17',
                'end_date' => '2024-10-13',
            ],
            // [
            //     'id' => 25671,
            //     'year' => 2567,
            //     'semesters' => 1,
            //     'start_date' => '2023-06-17',
            //     'end_date' => '2023-10-30',
            // ]
        ];

        foreach ($semesters as $key => $value) {
            Semesters::create($value);
        }
    }
}
