<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Models\Curriculums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MajorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curriculums = Curriculums::first();

        $majors = [
            [
                'name_th' => 'ปริญญาตรี ภาคปกติ',
                'name_en' => 'Bachelor`s degree, Normal program',
                'major_type' => 'N',
                'cur_id' => 1,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคพิเศษ',
                'name_en' => 'Bachelor`s degree, Special program',
                'major_type' => 'S',
                'cur_id' => 1,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคปกติ',
                'name_en' => 'Bachelor`s degree, Normal program',
                'major_type' => 'N',
                'cur_id' => 2,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคพิเศษ',
                'name_en' => 'Bachelor`s degree, Special program',
                'major_type' => 'S',
                'cur_id' => 2,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคปกติ',
                'name_en' => 'Bachelor`s degree, Normal program',
                'major_type' => 'N',
                'cur_id' => 3,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคพิเศษ',
                'name_en' => 'Bachelor`s degree, Special program',
                'major_type' => 'S',
                'cur_id' => 3,
                'status' => 'A'
            ],[
                'name_th' => 'ปริญญาตรี ภาคปกติ',
                'name_en' => 'Bachelor`s degree, Normal program',
                'major_type' => 'N',
                'cur_id' => 4,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคพิเศษ',
                'name_en' => 'Bachelor`s degree, Special program',
                'major_type' => 'S',
                'cur_id' => 4,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคปกติ',
                'name_en' => 'Bachelor`s degree, Normal program',
                'major_type' => 'N',
                'cur_id' => 5,
                'status' => 'A'
            ],
            [
                'name_th' => 'ปริญญาตรี ภาคพิเศษ',
                'name_en' => 'Bachelor`s degree, Special program',
                'major_type' => 'S',
                'cur_id' => 5,
                'status' => 'A'
            ],
        ];

        foreach ($majors as $key => $value) {
            Major::create($value);
        }
    }
}