<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TeachersTableSeeder extends Seeder
{
    public function run()
    {
        // ดึงข้อมูลจาก API
        $response = Http::get('https://tdbm.computing.kku.ac.th/api/get_data?table_name=teachers');
        $teachers = $response->json();

        // ปิด foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ลบข้อมูลเก่าโดยใช้ delete แทน truncate
        Teachers::query()->delete();

        // สร้างข้อมูลใหม่
        foreach ($teachers as $teacher) {
            // ตรวจสอบว่ามี user ที่มีอีเมลตรงกันหรือไม่
            $user = DB::table('users')->where('email', $teacher['email'])->first();

            // ถ้ามี user อยู่แล้ว ให้สร้างข้อมูล teacher
            if ($user) {
                Teachers::create([
                    'teacher_id' => $teacher['teacher_id'],
                    'prefix' => $teacher['prefix'],
                    'position' => $teacher['position'],
                    'degree' => $teacher['degree'],
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                    'user_id' => $user->id,
                ]);
            }
        }

        // เปิด foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
