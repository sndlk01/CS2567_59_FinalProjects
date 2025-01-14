<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers;
use App\Models\Curriculums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Services\TDBMApiService;
use Carbon\Carbon;

class CurriculumsTableSeeder extends Seeder
{
    public function run()
    {
        $apiService = new TDBMApiService();
        $curriculums = $apiService->getCurriculums();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('curriculums')->delete();

        foreach ($curriculums as $curriculum) {
            DB::table('curriculums')->insert([
                'cur_id' => $curriculum['cur_id'],
                'name_th' => $curriculum['name_th'],
                'name_en' => $curriculum['name_en'],
                'head_teacher_id' => $curriculum['head_teacher_id'],
                'created_at' => $curriculum['created_at'] ?? Carbon::now(),
                'updated_at' => $curriculum['updated_at'] ?? Carbon::now(),
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
