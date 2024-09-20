<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers;
use App\Models\Curriculums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CurriculumsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = Teachers::first();

        // if ($teachers) {
        $curriculums = [
            [
                'name_th' => 'วิทยาการคอมพิวเตอร์ ปริญญาตรี',
                'name_en' => 'B.Sc. (Computer Science)',
                'head_teacher_id' => 18, //อาจารย์คำรณ id = 4
            ],
            [
                'name_th' => 'เทคโนโลยีสารสนเทศ ปริญญาตรี',
                'name_en' => 'B.Sc. (Information Technology)',
                'head_teacher_id' => 24, //อาจารย์มัลลิกา
            ],
            [
                'name_th' => 'ภูมิสารสนเทศศาสตร์ ปริญญาตรี',
                'name_en' => 'B.Sc. (Geo-Informatics)',
                'head_teacher_id' => 19, //อาจารย์อุราวรรณ
            ],
            [
                'name_th' => 'ปัญญาประดิษฐ์ ปริญญาตรี',
                'name_en' => 'B.Sc. (Artificial Intelligence)',
                'head_teacher_id' => 25, //อาจารย์ไพรสันต์
            ],
            [
                'name_th' => 'ความมั่นคงปลอดภัยไซเบอร์ ปริญญาตรี',
                'name_en' => 'B.Sc. (Cybersecurity)',
                'head_teacher_id' => 31, //อาจารย์เพชร
            ],
        ];

        foreach ($curriculums as $curriculum) {
            Curriculums::create($curriculum);
        }
        // DB::table('curriculums')->insert($curriculums);
        // } else {
        //     $this->command->info('No teacher found. Please run TeachersSeeder first.');
        // }
    }
}
