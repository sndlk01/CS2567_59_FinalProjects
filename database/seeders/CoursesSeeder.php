<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Courses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            // CS
            // Data Structure
            [
                'status' => 'A',
                'subject_id' => 'CP352001',
                'owner_teacher_id' => 24,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            //User Experience Design
            [
                'status' => 'A',
                'subject_id' =>  'CP352002',
                'owner_teacher_id' => 12,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Web Programming and Application
            [
                'status' => 'A',
                'subject_id' => 'CP351203',
                'owner_teacher_id' => 30,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Operating Systems and System Calls Programming
            [
                'status' => 'A',
                'subject_id' =>  'CP353001',
                'owner_teacher_id' => 15,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Fundamental Computer Science
            [
                'status' => 'A',
                'subject_id' => 'CP351001',
                'owner_teacher_id' => 16,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Software Quality Assurance
            [
                'status' => 'A',
                'subject_id' =>  'CP353201',
                'owner_teacher_id' => 4,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Neural Network and Deep Learning
            [
                'status' => 'A',
                'subject_id' => 'CP353108',
                'owner_teacher_id' => 37,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Artificial Intelligence
            [
                'status' => 'A',
                'subject_id' =>  'CP353003',
                'owner_teacher_id' => 26,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Introduction to Machine Learning
            [
                'status' => 'A',
                'subject_id' => 'CP353102',
                'owner_teacher_id' => 36,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],
            // Data Analytics and Application
            [
                'status' => 'A',
                'subject_id' =>  'CP353103',
                'owner_teacher_id' => 8,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Web Design Technologies
            [
                'status' => 'A',
                'subject_id' => 'CP352201',
                'owner_teacher_id' => 21,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Structure Programming Languages for Computer Science
            [
                'status' => 'A',
                'subject_id' =>  'CP351002',
                'owner_teacher_id' => 3,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Seminar in Computer Science
            [
                'status' => 'A',
                'subject_id' => 'CP353761',
                'owner_teacher_id' => 3,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // Principles of Software Design and Development 
            [
                'status' => 'A',
                'subject_id' =>  'CP353002',
                'owner_teacher_id' => 10,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],
            [
                'status' => 'A',
                'subject_id' =>  'SC313002',
                'owner_teacher_id' => 10,
                'semesters_id' => 25671,
                'major_id' => 1,
                'cur_id' => 1,
            ],

            // IT
            //Structured Programming for Information Technology
            [
                'status' => 'A',
                'subject_id' =>  'SC361002',
                'owner_teacher_id' => 30,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Web Application Programming
            [
                'status' => 'A',
                'subject_id' =>  'SC362004',
                'owner_teacher_id' => 30,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Research Methodology
            [
                'status' => 'A',
                'subject_id' =>  'SC363762',
                'owner_teacher_id' => 30,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Introduction to Computer Networking
            [
                'status' => 'A',
                'subject_id' =>  'SC362003',
                'owner_teacher_id' => 9,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Introduction to Computer Networking
            [
                'status' => 'A',
                'subject_id' =>  '342222',
                'owner_teacher_id' => 9,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Database Analysis and Design
            [
                'status' => 'A',
                'subject_id' =>  'SC362005',
                'owner_teacher_id' => 25,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Software Testing and Quality Assurance
            [
                'status' => 'A',
                'subject_id' =>  'SC363101',
                'owner_teacher_id' => 14,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Strategic Management of Information Technology
            [
                'status' => 'A',
                'subject_id' =>  'SC363002',
                'owner_teacher_id' => 23,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],
            // Business Intelligence System
            [
                'status' => 'A',
                'subject_id' =>  'SC363102',
                'owner_teacher_id' => 23,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Software Engineering
            [
                'status' => 'A',
                'subject_id' =>  'SC362102',
                'owner_teacher_id' => 31,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Systems Analysis and Design
            [
                'status' => 'A',
                'subject_id' =>  'SC363001',
                'owner_teacher_id' => 31,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Inspiration in IT Career
            [
                'status' => 'A',
                'subject_id' =>  'SC361001',
                'owner_teacher_id' => 14,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Operating Systems
            [
                'status' => 'A',
                'subject_id' =>  'SC362001',
                'owner_teacher_id' => 15,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Data Serialization Languages and Applications
            [
                'status' => 'A',
                'subject_id' =>  'CP363205',
                'owner_teacher_id' => 11,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Introduction To Information And Communication Technology
            [
                'status' => 'A',
                'subject_id' =>  'SC320001',
                'owner_teacher_id' => 32,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Web Design Technologies
            [
                'status' => 'A',
                'subject_id' =>  'SC362201',
                'owner_teacher_id' => 21,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Digital Logic and Embedded Systems
            [
                'status' => 'A',
                'subject_id' =>  'SC362301',
                'owner_teacher_id' => 22,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // Information Technology Profession
            [
                'status' => 'A',
                'subject_id' =>  'SC363003',
                'owner_teacher_id' => 22,
                'semesters_id' => 25671,
                'major_id' => 3,
                'cur_id' => 2,
            ],

            // GIS
            // Data Structure
            [
                'status' => 'A',
                'subject_id' =>  'CP350002',
                'owner_teacher_id' => 12,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // Basics of Information Technology
            [
                'status' => 'A',
                'subject_id' =>  'CP371031',
                'owner_teacher_id' => 2,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // Introduction to Geographic Information System
            [
                'status' => 'A',
                'subject_id' =>  'CP372002',
                'owner_teacher_id' => 6,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // Fundamentals of Remote Sensing
            [
                'status' => 'A',
                'subject_id' =>  'CP372011',
                'owner_teacher_id' => 20,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // Spatial Database Management
            [
                'status' => 'A',
                'subject_id' =>  'CP373232',
                'owner_teacher_id' => 2,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // Unmanned Aerial Vehicle Mapping
            [
                'status' => 'A',
                'subject_id' =>  'CP373112',
                'owner_teacher_id' => 6,
                'semesters_id' => 25671,
                'major_id' => 5,
                'cur_id' => 3,
            ],

            // AI
            // Programming for Machine Learning
            [
                'status' => 'A',
                'subject_id' =>  'CP411106',
                'owner_teacher_id' => 29,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],
            // Artificial Intelligence Inspiration
            [
                'status' => 'A',
                'subject_id' =>  'CP411701',
                'owner_teacher_id' => 26,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // Artificial Intelligence Workshop I
            [
                'status' => 'A',
                'subject_id' =>  'CP412703',
                'owner_teacher_id' => 26,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // Machine Learning
            [
                'status' => 'A',
                'subject_id' =>  'CP412002',
                'owner_teacher_id' => 36,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // Data Science
            [
                'status' => 'A',
                'subject_id' =>  'CP412003',
                'owner_teacher_id' => 37,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // Neural Network and Deep Learning
            [
                'status' => 'A',
                'subject_id' =>  'CP412004',
                'owner_teacher_id' => 37,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // Data Structures and Algorithms
            [
                'status' => 'A',
                'subject_id' =>  'CP411107',
                'owner_teacher_id' => 22,
                'semesters_id' => 25671,
                'major_id' => 7,
                'cur_id' => 4,
            ],

            // CY
            // Introduction to Computer Networking
            [
                'status' => 'A',
                'subject_id' =>  'CP422011',
                'owner_teacher_id' => 9,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],
            // Software Design and Analysis
            [
                'status' => 'A',
                'subject_id' =>  'CP421025',
                'owner_teacher_id' => 31,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],

            // Database Architecture Analysis and Design
            [
                'status' => 'A',
                'subject_id' =>  'CP422022',
                'owner_teacher_id' => 29,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],

            // Cybersecurity Law and Ethic
            [
                'status' => 'A',
                'subject_id' =>  'CP422001',
                'owner_teacher_id' => 5,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],

            // Introduction to Operating System
            [
                'status' => 'A',
                'subject_id' =>  'CP422052',
                'owner_teacher_id' => 5,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],

            // Inspiration in Cybersecurity Career
            [
                'status' => 'A',
                'subject_id' =>  'CP421011',
                'owner_teacher_id' => 32,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],

            // Web and Mobile Application Architecture
            [
                'status' => 'A',
                'subject_id' =>  'CP422021',
                'owner_teacher_id' => 32,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],
            // Structured Programming
            [
                'status' => 'A',
                'subject_id' =>  'CP421021',
                'owner_teacher_id' => 35,
                'semesters_id' => 25671,
                'major_id' => 9,
                'cur_id' => 5,
            ],
        ];

        foreach ($courses as $course) {
            if (DB::table('subjects')->where('subject_id', $course['subject_id'])->exists()) {
                Courses::create($course);
            } else {
                // สามารถเลือกที่จะ log ข้อมูล หรือแจ้งเตือนถ้า subject_id นั้นไม่มีในตาราง subjects
                Log::warning('Subject ID ' . $course['subject_id'] . ' does not exist in the subjects table.');
            }
        }

        // foreach ($courses as $key =>$value){
        //     Courses::create($value);
        // }
    }
}
