<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Requests;
use App\Models\Curriculums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Request;

class RequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $requests = [
            [
                'student_id' => 1,
                'course_id' => 1,
                'status' => 'N',
                'comment' => 'Request pending approval',
                'approved_at' => null,
            ],
        ];

        foreach ($requests as $key => $value) {
            Requests::create($value);
        }
    }
}