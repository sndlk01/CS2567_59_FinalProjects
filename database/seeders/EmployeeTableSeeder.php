<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $employee = [
        //     'fname' => 'Satida',
        //     'lname' => 'Wetchatsart',
        //     'email' => 'satida@admin.com',
        //     'phone' => '0812345678',
        //     // 'user_id' => 1,
        // ];

        // // foreach ($employees as $employee) {
        // $user = DB::table('users')->where('email', $employee['email'])->first();

        // if ($user) {
        //     DB::table('employee')->insert([
        //         'fname' => $employee['fname'],
        //         'lname' => $employee['lname'],
        //         'email' => $employee['email'],
        //         'phone' => $employee['phone'],
        //         'user_id' => $user->id,
        //     ]);
        // }
        Employee::create([
            'fname' => 'Satida',
            'lname' => 'Wetchatsart',
            'email' => 'satida@admin.com',
            'phone' => '0812345678',
            'user_id' => 1,
        ]);

    }
}
