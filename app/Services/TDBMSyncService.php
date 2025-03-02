<?php

namespace App\Services;

use App\Models\{Teachers, Curriculums, Major, Subjects, Courses, Semesters, Classes, Teaching, ExtraTeaching, User};
use Illuminate\Support\Facades\{DB, Log, File};

class TDBMSyncService
{
    private $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->tdbmService = $tdbmService;
    }

    private function logTransactionLevel($message)
    {
        Log::info($message . " - Transaction level: " . DB::transactionLevel());
    }

    public function syncAll()
    {
        $logFile = storage_path('logs/sync_' . date('Y-m-d') . '.log');
        File::put($logFile, "Starting sync at " . date('Y-m-d H:i:s') . "\n");

        try {
            // Start a single transaction for everything
            $this->logTransactionLevel("Before main transaction begin");
            DB::beginTransaction();
            $this->logTransactionLevel("After main transaction begin");

            // ซิงค์ตามลำดับการพึ่งพา
            $this->syncTeachers();
            $this->logTransactionLevel("After syncTeachers");

            $this->syncCurriculums();
            $this->logTransactionLevel("After syncCurriculums");

            $this->syncMajors();
            $this->logTransactionLevel("After syncMajors");

            $this->syncSubjects();
            $this->logTransactionLevel("After syncSubjects");

            $this->syncSemesters();
            $this->logTransactionLevel("After syncSemesters");

            $this->syncCourses();
            $this->logTransactionLevel("After syncCourses");

            $this->syncClasses();
            $this->logTransactionLevel("After syncClasses");

            $this->syncTeachings();
            $this->logTransactionLevel("After syncTeachings");

            $this->syncExtraTeachings();
            $this->logTransactionLevel("After syncExtraTeachings");

            // Commit everything at once
            $this->logTransactionLevel("Before main transaction commit");
            DB::commit();
            $this->logTransactionLevel("After main transaction commit");

            File::append($logFile, "Sync completed at " . date('Y-m-d H:i:s') . "\n");
            return true;
        } catch (\Exception $e) {
            $this->logTransactionLevel("In exception handler before rollback");
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->logTransactionLevel("After rollback");

            File::append($logFile, "Sync failed: " . $e->getMessage() . "\n");
            throw $e;
        }
    }

    private function updateOrCreateRecord($model, $identifier, $data, $dependencies = [])
    {
        foreach ($dependencies as $key => $value) {
            if (!$value) {
                Log::warning("{$model} sync: Missing dependency {$key}");
                return false;
            }
        }

        try {
            $record = $model::updateOrCreate($identifier, $data);
            return $record;
        } catch (\Exception $e) {
            Log::error("{$model} sync failed: " . $e->getMessage());
            return false;
        }
    }

    private function validateApiData($data, $required = [])
    {
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        return true;
    }

    private function syncTeachers()
    {
        try {
            $teachers = collect($this->tdbmService->getTeachers());
            Log::info("Fetched " . $teachers->count() . " teachers from API");

            // Get all users with their emails (case-insensitive key)
            $users = User::all();
            $usersByEmail = [];
            foreach ($users as $user) {
                if ($user->email) {
                    $usersByEmail[strtolower($user->email)] = $user;
                }
            }

            Log::info("Found " . count($users) . " users in database");

            $syncCount = 0;
            $errorCount = 0;
            $validTeachers = [];

            // Validate all teacher data first
            foreach ($teachers as $teacher) {
                if (!$this->validateApiData($teacher, ['teacher_id', 'name'])) {
                    Log::warning("Teacher data validation failed: " . json_encode($teacher));
                    $errorCount++;
                    continue;
                }

                // Find a matching user by email only
                $userId = null;

                if (!empty($teacher['email'])) {
                    $teacherEmail = strtolower($teacher['email']);
                    if (isset($usersByEmail[$teacherEmail])) {
                        $userId = $usersByEmail[$teacherEmail]->id;
                        Log::info("Teacher {$teacher['name']} matched with user ID: {$userId} by email: {$teacher['email']}");
                    } else {
                        Log::warning("Teacher {$teacher['name']} has email {$teacher['email']} but no matching user found");
                    }
                } else {
                    Log::info("Teacher {$teacher['name']} (ID: {$teacher['teacher_id']}) has no email, setting user_id to null");
                }

                // Add to valid teachers array with the matched user_id (or null)
                $teacher['matched_user_id'] = $userId;
                $validTeachers[] = $teacher;
            }

            // If we have valid teachers to sync, proceed with truncate and insert
            if (count($validTeachers) > 0) {
                // Now it's safe to truncate because we've validated all data
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Teachers::truncate();

                // Insert all valid teachers
                foreach ($validTeachers as $teacher) {
                    try {
                        // สร้างข้อมูลพื้นฐานของอาจารย์
                        $userData = [
                            'teacher_id' => $teacher['teacher_id'],
                            'prefix' => $teacher['prefix'] ?? '',
                            'position' => $teacher['position'] ?? '',
                            'degree' => $teacher['degree'] ?? '',
                            'name' => $teacher['name'],
                            'email' => $teacher['email'] ?? null
                        ];

                        // เพิ่ม user_id เฉพาะเมื่อมีการจับคู่ได้เท่านั้น
                        if ($teacher['matched_user_id'] !== null) {
                            $userData['user_id'] = $teacher['matched_user_id'];
                        }
                        // ถ้าไม่มีการจับคู่ ไม่ต้องใส่ค่า user_id (จะเป็น NULL โดยอัตโนมัติ)

                        Teachers::create($userData);
                        $syncCount++;
                    } catch (\Exception $e) {
                        Log::error("Failed to create teacher record: " . $e->getMessage() . " for teacher: " . json_encode($userData));
                        $errorCount++;
                    }
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                Log::info("Teachers sync completed successfully: {$syncCount} synced, {$errorCount} errors");
            } else {
                throw new \Exception("No valid teachers to sync");
            }
        } catch (\Exception $e) {
            Log::error("Teacher sync failed: " . $e->getMessage());
            throw $e;
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
        try {
            $majors = collect($this->tdbmService->getMajors());
            Log::info("Fetched " . $majors->count() . " majors from API");

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Major::truncate(); // ล้างข้อมูลเดิมและ reset auto increment

            $syncCount = 0;
            $errorCount = 0;

            foreach ($majors as $major) {
                try {
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (!$this->validateApiData($major, ['major_id', 'name_th', 'major_type', 'cur_id'])) {
                        Log::warning("Major data validation failed: " . json_encode($major));
                        $errorCount++;
                        continue;
                    }

                    // ตรวจสอบการมีอยู่ของ curriculum
                    $curriculum = Curriculums::find($major['cur_id']);
                    if (!$curriculum) {
                        Log::warning("Major sync: Curriculum {$major['cur_id']} not found for major {$major['major_id']}");
                        $errorCount++;
                        continue;
                    }

                    Major::create([
                        'major_id' => $major['major_id'], // ใช้ major_id จาก API โดยตรง
                        'name_th' => $major['name_th'],
                        'name_en' => $major['name_en'] ?? null,
                        'major_type' => $major['major_type'],
                        'cur_id' => $major['cur_id'],
                        'status' => $major['status'] ?? 'A'
                    ]);
                    $syncCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync major ID {$major['major_id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info("Majors sync completed: {$syncCount} synced, {$errorCount} errors");
        } catch (\Exception $e) {
            Log::error("Major sync failed: " . $e->getMessage());
            throw $e;
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
        try {
            $courses = collect($this->tdbmService->getCourses());
            Log::info("Fetched " . $courses->count() . " courses from API");

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Courses::truncate(); // เพิ่มการ truncate ตาราง

            $syncCount = 0;
            $errorCount = 0;

            foreach ($courses as $course) {
                try {
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (!$this->validateApiData($course, ['course_id', 'subject_id', 'owner_teacher_id', 'semester_id'])) {
                        Log::warning("Course data validation failed: " . json_encode($course));
                        $errorCount++;
                        continue;
                    }

                    // ตรวจสอบความสัมพันธ์
                    $subject = Subjects::where('subject_id', $course['subject_id'])->first();
                    $teacher = Teachers::find($course['owner_teacher_id']);
                    $semester = Semesters::find($course['semester_id']);

                    if (!$subject || !$teacher || !$semester) {
                        Log::warning("Course {$course['course_id']} missing dependencies - Subject: {$course['subject_id']}, Teacher: {$course['owner_teacher_id']}, Semester: {$course['semester_id']}");
                        $errorCount++;
                        continue;
                    }

                    // เช็คความมีอยู่ของ curriculum ถ้ามี
                    $curriculum = null;
                    if (!empty($course['cur_id'])) {
                        $curriculum = Curriculums::find($course['cur_id']);
                        if (!$curriculum) {
                            Log::warning("Course {$course['course_id']} references non-existent curriculum {$course['cur_id']}");
                            continue;
                        }
                    }

                    // ทำการสร้างข้อมูล
                    Courses::create([
                        'course_id' => (string)$course['course_id'],
                        'status' => $course['status'] ?? 'A',
                        'subject_id' => $course['subject_id'],
                        'owner_teacher_id' => $course['owner_teacher_id'],
                        'semester_id' => $course['semester_id'],
                        'major_id' => $course['major_id'] ?? null,
                        'cur_id' => $course['cur_id'] ?? null
                    ]);

                    $syncCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync course ID {$course['course_id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info("Courses sync completed: {$syncCount} synced, {$errorCount} errors");
        } catch (\Exception $e) {
            Log::error("Course sync failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function syncClasses()
    {
        try {
            $classes = collect($this->tdbmService->getStudentClasses());

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($classes as $class) {
                // Verify dependencies
                $course = Courses::where('course_id', $class['course_id'])->first();
                $teacher = Teachers::where('teacher_id', $class['teacher_id'])->first();

                if (!$course || !$teacher) {
                    Log::warning("Class {$class['class_id']} dependencies check failed - Course: {$class['course_id']}, Teacher: {$class['teacher_id']}");
                    Log::info("Full class data: " . json_encode($class));
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

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            Log::error("Classes sync failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function syncTeachings()
    {
        try {
            $teachings = collect($this->tdbmService->getTeachings());

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($teachings as $teaching) {
                // Verify dependencies
                $class = Classes::where('class_id', $teaching['class_id'])->first();
                $teacher = Teachers::where('teacher_id', $teaching['teacher_id'])->first();

                if (!$class || !$teacher) {
                    Log::warning("Teaching {$teaching['teaching_id']} dependencies check failed - Class: {$teaching['class_id']}, Teacher: {$teaching['teacher_id']}");
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

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            Log::error("Teaching sync failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function syncExtraTeachings()
    {
        try {
            $extraTeachings = collect($this->tdbmService->getExtraTeachings());
            Log::info("Fetched " . $extraTeachings->count() . " extra teachings from API");

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $syncCount = 0;
            $errorCount = 0;

            foreach ($extraTeachings as $teaching) {
                // Verify dependencies
                $mainTeaching = Teaching::find($teaching['teaching_id']);
                $class = Classes::find($teaching['class_id']);
                $teacher = Teachers::find($teaching['teacher_id']);

                if (!$mainTeaching || !$class || !$teacher) {
                    Log::warning("ExtraTeaching {$teaching['extra_class_id']} missing dependencies");
                    $errorCount++;
                    continue;
                }

                try {
                    ExtraTeaching::updateOrCreate(
                        ['extra_class_id' => $teaching['extra_class_id']],
                        [
                            'title' => $teaching['title'] ?? 'No Title',
                            'detail' => $teaching['detail'] ?? 'No Detail',
                            'opt_status' => $teaching['opt_status'] ?? 'A',
                            'status' => $teaching['status'] ?? 'A',
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
                    $syncCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync extra teaching: {$teaching['extra_class_id']}, Error: " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info("Extra teachings sync completed: {$syncCount} synced, {$errorCount} errors");
        } catch (\Exception $e) {
            Log::error("Extra teaching sync failed: " . $e->getMessage());
            throw $e;
        }
    }
}
