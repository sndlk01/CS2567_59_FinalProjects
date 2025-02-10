<?php

namespace App\Services;

use App\Models\{Teachers, Curriculums, Major, Subjects, Courses, Semesters, Classes, Teaching, ExtraTeaching, User};
use Illuminate\Support\Facades\{DB, Log};

class TDBMSyncService
{
    private $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->tdbmService = $tdbmService;
    }

    public function syncAll()
    {
        try {
            DB::beginTransaction();

            $this->syncTeachers();        // 1. สร้าง Teachers ก่อน
            $this->syncCurriculums();     // 2. จึงสร้าง Curriculums ที่อ้างอิง teacher_id ได้
            $this->syncMajors();          // 3. Majors อ้างอิง curriculum_id
            $this->syncSubjects();        // 4. Subjects อ้างอิง curriculum_id
            $this->syncSemesters();       // 5. Semesters อิสระ
            $this->syncCourses();         // 6. Courses อ้างอิง teachers, subjects, semesters
            $this->syncClasses();         // 7. Classes อ้างอิง courses
            $this->syncTeachings();       // 8. Teaching อ้างอิง classes
            $this->syncExtraTeachings();  // 9. ExtraTeaching อ้างอิง teaching

            DB::commit();
            Log::info('TDBM data sync completed successfully');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TDBM sync failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function syncTeachers()
    {
        $teachers = collect($this->tdbmService->getTeachers());

        foreach ($teachers as $teacher) {
            // หา user จาก email
            $user = User::where('email', $teacher['email'])->first();

            if ($user) {
                Teachers::updateOrCreate(
                    ['teacher_id' => $teacher['teacher_id']],
                    [
                        'prefix' => $teacher['prefix'],
                        'position' => $teacher['position'],
                        'degree' => $teacher['degree'],
                        'name' => $teacher['name'],
                        'email' => $teacher['email'],
                        'user_id' => $user->id
                    ]
                );
            }
        }
    }

    private function syncCurriculums()
    {
        $curriculums = collect($this->tdbmService->getCurriculums());

        foreach ($curriculums as $curriculum) {
            // ตรวจสอบว่ามี teacher ที่จะอ้างอิงหรือไม่
            $teacher = Teachers::find($curriculum['head_teacher_id']);
            if (!$teacher && $curriculum['head_teacher_id'] !== null) {
                Log::warning("Curriculum {$curriculum['cur_id']} references non-existent teacher {$curriculum['head_teacher_id']}, skipping...");
                continue;
            }

            Curriculums::updateOrCreate(
                ['cur_id' => $curriculum['cur_id']],
                [
                    'name_th' => $curriculum['name_th'],
                    'name_en' => $curriculum['name_en'],
                    'head_teacher_id' => $curriculum['head_teacher_id'],
                    'curr_type' => $curriculum['curr_type']
                ]
            );
        }
    }

    private function syncMajors()
    {
        $majors = collect($this->tdbmService->getMajors());

        foreach ($majors as $major) {
            // ตรวจสอบการมีอยู่ของ curriculum
            $curriculum = Curriculums::find($major['cur_id']);
            if (!$curriculum) {
                Log::warning("Major {$major['major_id']} references non-existent curriculum {$major['cur_id']}, skipping...");
                continue;
            }

            Major::updateOrCreate(
                ['major_id' => $major['major_id']],
                [
                    'name_th' => $major['name_th'],
                    'name_en' => $major['name_en'] ?? null,
                    'major_type' => $major['major_type'],
                    'cur_id' => $major['cur_id'],
                    'status' => $major['status']
                ]
            );
        }
    }

    private function syncSubjects()
    {
        $subjects = collect($this->tdbmService->getSubjects());

        foreach ($subjects as $subject) {
            // ตรวจสอบข้อมูลที่จำเป็นและการมีอยู่ของ curriculum
            if (
                !isset($subject['subject_id']) || !isset($subject['name_th']) ||
                !isset($subject['name_en']) || !isset($subject['credit']) ||
                !isset($subject['cur_id'])
            ) {
                continue;
            }

            $curriculum = Curriculums::find($subject['cur_id']);
            if (!$curriculum) {
                Log::warning("Subject {$subject['subject_id']} references non-existent curriculum {$subject['cur_id']}, skipping...");
                continue;
            }

            Subjects::updateOrCreate(
                ['subject_id' => $subject['subject_id']],
                [
                    'name_th' => $subject['name_th'],
                    'name_en' => $subject['name_en'],
                    'credit' => $subject['credit'],
                    'weight' => $subject['weight'] ?? null,
                    'detail' => $subject['detail'] ?? null,
                    'cur_id' => $subject['cur_id'],
                    'status' => $subject['status'] ?? 'A'
                ]
            );
        }
    }

    private function syncSemesters()
    {
        $semesters = collect($this->tdbmService->getSemesters());

        foreach ($semesters as $semester) {
            Semesters::updateOrCreate(
                ['semester_id' => $semester['semester_id']],
                [
                    'year' => $semester['year'],
                    'semesters' => $semester['semester'],
                    'start_date' => $semester['start_date'],
                    'end_date' => $semester['end_date']
                ]
            );
        }
    }

    private function syncCourses()
    {
        $courses = collect($this->tdbmService->getCourses());

        foreach ($courses as $course) {
            // ตรวจสอบข้อมูลที่จำเป็น
            if (
                !isset($course['course_id']) || !isset($course['subject_id']) ||
                !isset($course['owner_teacher_id']) || !isset($course['semester_id']) ||
                !isset($course['cur_id'])
            ) {
                continue;
            }

            // ตรวจสอบ subject
            $subject = Subjects::find($course['subject_id']);
            if (!$subject) {
                Log::warning("Course {$course['course_id']} references non-existent subject {$course['subject_id']}, skipping...");
                continue;
            }

            // ตรวจสอบ teacher
            $teacher = Teachers::find($course['owner_teacher_id']);
            if (!$teacher) {
                Log::warning("Course {$course['course_id']} references non-existent teacher {$course['owner_teacher_id']}, skipping...");
                continue;
            }

            Courses::updateOrCreate(
                ['course_id' => $course['course_id']],
                [
                    'status' => $course['status'] ?? 'A',
                    'subject_id' => $course['subject_id'],
                    'owner_teacher_id' => $course['owner_teacher_id'],
                    'semester_id' => $course['semester_id'],
                    'major_id' => $course['major_id'] ?? null,
                    'cur_id' => $course['cur_id']
                ]
            );
        }
    }

    private function syncClasses()
    {
        $classes = collect($this->tdbmService->getStudentClasses());

        foreach ($classes as $class) {
            // ตรวจสอบ teacher
            $teacher = Teachers::find($class['teacher_id']);
            if (!$teacher) {
                Log::warning("Class {$class['class_id']} missing teacher {$class['teacher_id']}, skipping...");
                continue;
            }

            // ตรวจสอบ course
            $course = Courses::find($class['course_id']);
            if (!$course) {
                Log::warning("Class {$class['class_id']} missing course {$class['course_id']}, skipping...");
                continue;
            }

            Classes::updateOrCreate(
                ['class_id' => $class['class_id']],
                [
                    'section_num' => $class['section_num'],
                    'title' => $class['title'],
                    'open_num' => $class['open_num'],
                    'enrolled_num' => $class['enrolled_num'],
                    'available_num' => $class['available_num'],
                    'teacher_id' => $class['teacher_id'],
                    'course_id' => $class['course_id'],
                    'semester_id' => $class['semester_id'],
                    'major_id' => $class['major_id'],
                    'status' => $class['status']
                ]
            );
        }
    }

    private function syncTeachings()
    {
        $teachings = collect($this->tdbmService->getTeachings());

        foreach ($teachings as $teaching) {
            // ตรวจสอบการมีอยู่ของ class
            $class = Classes::find($teaching['class_id']);
            if (!$class) {
                Log::warning("Teaching {$teaching['teaching_id']} references non-existent class {$teaching['class_id']}, skipping...");
                continue;
            }

            // ตรวจสอบการมีอยู่ของ teacher
            $teacher = Teachers::find($teaching['teacher_id']);
            if (!$teacher) {
                Log::warning("Teaching {$teaching['teaching_id']} references non-existent teacher {$teaching['teacher_id']}, skipping...");
                continue;
            }

            Teaching::updateOrCreate(
                ['teaching_id' => $teaching['teaching_id']],
                [
                    'start_time' => $teaching['start_time'],
                    'end_time' => $teaching['end_time'],
                    'duration' => $teaching['duration'],
                    'class_type' => $teaching['class_type'],
                    'status' => $teaching['status'],
                    'class_id' => $teaching['class_id'],
                    'teacher_id' => $teaching['teacher_id']
                ]
            );
        }
    }

    private function syncExtraTeachings()
    {
        $extraTeachings = collect($this->tdbmService->getExtraTeachings());

        foreach ($extraTeachings as $teaching) {
            // ตรวจสอบข้อมูลที่จำเป็น
            if (!isset($teaching['title']) || !isset($teaching['detail'])) {
                Log::warning("Extra teaching {$teaching['extra_class_id']} missing required fields, skipping...");
                continue;
            }

            ExtraTeaching::updateOrCreate(
                ['extra_class_id' => $teaching['extra_class_id']],
                [
                    'title' => $teaching['title'] ?? 'No Title',
                    'detail' => $teaching['detail'] ?? 'No Detail',
                    'opt_status' => $teaching['opt_status'],
                    'status' => $teaching['status'],
                    'class_date' => $teaching['class_date'],
                    'start_time' => $teaching['start_time'],
                    'end_time' => $teaching['end_time'],
                    'duration' => $teaching['duration'],
                    'teacher_id' => $teaching['teacher_id'],
                    'holiday_id' => $teaching['holiday_id'],
                    'teaching_id' => $teaching['teaching_id'],
                    'class_id' => $teaching['class_id']
                ]
            );
        }
    }
}
