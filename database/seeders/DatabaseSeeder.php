<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Subjects;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(CreateUsersSeeder::class);
        $this->call(EmployeeTableSeeder::class);
        $this->call(TeachersTableSeeder::class);
        $this->call(CurriculumsTableSeeder::class);
        $this->call(MajorTableSeeder::class);
        $this->call(SubjectsSeeder::class);
        $this->call(SemestersTableSeeder::class);
        $this->call(ClassTypeTableSeeder::class);
        $this->call(CurriculumsSubjectsSeeder::class);
        $this->call(CoursesSeeder::class);
        $this->call(ClassesSeeder::class);
        $this->call(TeachingSeeder::class);
        $this->call(AnnouncesSeeder::class);
    }
}
