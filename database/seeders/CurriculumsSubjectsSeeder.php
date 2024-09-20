<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CurriculumsSubjects;



class CurriculumsSubjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CurriculumsSubjects::create([
            'cur_id' => 1,
            'subject_id' => 'CP352001',
        ]);

    }
}
