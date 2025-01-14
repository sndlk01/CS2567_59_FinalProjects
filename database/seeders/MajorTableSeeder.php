<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Models\Curriculums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Services\TDBMApiService;
use Carbon\Carbon;


class MajorTableSeeder extends Seeder
{
    public function run()
    {
        $apiService = new TDBMApiService();
        $majors = $apiService->getMajors();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('major')->delete(); // เปลี่ยนจาก majors เป็น major ตาม migration

        foreach ($majors as $major) {
            DB::table('major')->insert([  // เปลี่ยนจาก majors เป็น major
                'major_id' => $major['major_id'],
                'name_th' => $major['name_th'],
                'name_en' => $major['name_en'],
                'major_type' => $major['major_type'],
                'cur_id' => $major['cur_id'],
                'status' => $major['status'],
                'created_at' => $major['created_at'] ?? Carbon::now(),
                'updated_at' => $major['updated_at'] ?? Carbon::now(),
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}