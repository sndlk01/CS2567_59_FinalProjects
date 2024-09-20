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
                'title' => 'LAB',
            ],
            [
                'title' => 'LECTURE',
            ]
        ];

        foreach ($class_type as $key => $value) {
            ClassType::create($value);
        }

    }
}
