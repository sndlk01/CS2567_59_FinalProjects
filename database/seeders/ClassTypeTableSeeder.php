<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ClassTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class_type = [
            [
                'class_type_id' => 'L',
                'title' => 'LAB',
            ],
            [
                'class_type_id' => 'C',
                'title' => 'LEC',
            ]
        ];

        foreach ($class_type as $key => $value) {
            ClassType::create($value);
        }

    }
}
