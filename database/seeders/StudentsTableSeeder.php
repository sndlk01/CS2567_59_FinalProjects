<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Students;
use App\Models\Subjects;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subjects::first();
        $users = User::first();

        $students = [
            [
                'student_id' => '643021316-6',
                'prefix' => 'นาย',
                'fname' => 'ชาคริต',
                'lname' => 'ปรากฎ',
                'card_id' => '1234567890123',
                'phone' => '0823456789',
                'email' => 'chakit.p@kkumail.com',
            ],
            // [
            //     'student_id' => '643021342-5',
            //     'fName' => 'สุพัตรา',
            //     'lName' => 'แพงจันทร์',
            //     'card_id' => '9876543210987',
            //     'phone' => '0887654321',
            //     'email' => 'supattra.pa@kkumail.com',
            //     'user_id' => 5,
            //     'subjects_id' => $subjects->id,
            //     'type_ta' => false,
            //     'uploadfile' => 'null',
            // ],
        ];

        // foreach ($students as $key => $value) {
        //     Students::create($value);
        // }
        foreach ($students as $student) {
            $user = DB::table('users')->where('email', $student['email'])->first();

            if ($user) {
                Students::create([
                    'student_id' => $student['student_id'],
                    'prefix' => $student['prefix'],
                    'name' => $student['name'],
                    'card_id' => $student['card_id'],
                    'phone' => $student['phone'],
                    'email' => $student['email'],
                    // 'type_ta' => $student['type_ta'],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
