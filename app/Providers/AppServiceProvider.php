<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Teachers;
use App\Models\CourseTas;
use App\Models\Semesters;
use App\Models\Courses;
use App\Models\CompensationTransaction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('excel.helper', function ($app) {
            return new \App\Helpers\ExcelHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        View::composer('layouts.teacherLayout', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $teacher = Teachers::where('user_id', $user->id)->first();
                
                if ($teacher) {
                    try {
                        // ดึงข้อมูลภาคการศึกษาที่กำลังใช้งาน
                        $activeSemester = Semesters::where('active', true)->first(); // ปรับตามโครงสร้างของคุณ
                        
                        if ($activeSemester) {
                            // นับคำขอที่รออนุมัติ
                            $pendingRequestsCount = CourseTas::whereHas('course', function ($query) use ($teacher, $activeSemester) {
                                $query->where('owner_teacher_id', $teacher->teacher_id)
                                      ->where('semester_id', $activeSemester->semester_id);
                            })
                            ->whereHas('courseTaClasses.requests', function ($query) {
                                $query->where('status', 'W');
                            })
                            ->count();
                            
                            $view->with('pendingRequestsCount', $pendingRequestsCount);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error in view composer: ' . $e->getMessage());
                    }
                }
            }
        });

        View::composer('layouts.adminLayout', function ($view) {
            $pendingCount = 0;
            
            if (Auth::check()) {
                try {
                    $semester = $this->getActiveSemester();
                    
                    if ($semester) {
                        // สร้างช่วงเดือนในภาคการศึกษา
                        $start = \Carbon\Carbon::parse($semester->start_date);
                        $end = \Carbon\Carbon::parse($semester->end_date);
                        $months = [];

                        // สร้าง array ของทุกเดือนในภาคการศึกษา
                        $currentMonth = clone $start;
                        while ($currentMonth <= $end) {
                            $months[] = $currentMonth->format('Y-m');
                            $currentMonth->addMonth();
                        }
                        
                        // ดึง TA ทั้งหมดในภาคการศึกษานี้
                        $tas = CourseTas::whereHas('course', function($query) use ($semester) {
                            $query->where('semester_id', $semester->semester_id);
                        })->get();
        
                        // สร้าง set เพื่อเก็บคู่ student_id + course_id + month_year ที่นับแล้ว
                        $countedPairs = [];
                        
                        foreach ($tas as $ta) {
                            foreach ($months as $yearMonth) {
                                // สร้าง key ที่ไม่ซ้ำกันสำหรับแต่ละ TA + เดือน + รายวิชา
                                $key = $ta->student_id . '_' . $ta->course_id . '_' . $yearMonth;
                                
                                // ข้ามหากเคยนับแล้ว
                                if (in_array($key, $countedPairs)) {
                                    continue;
                                }
                                
                                // ตรวจสอบว่ามีการเบิกจ่ายแล้วหรือไม่
                                $hasPaid = CompensationTransaction::where('student_id', $ta->student_id)
                                    ->where('course_id', $ta->course_id)
                                    ->where('month_year', $yearMonth)
                                    ->exists();
                                
                                // ตรวจสอบว่ามีชั่วโมงทำงานในเดือนนี้หรือไม่
                                $hasWork = $this->hasAttendanceInMonth($ta->student_id, $ta->course_id, $yearMonth);
                                
                                // นับเฉพาะกรณีที่มีการทำงานแต่ยังไม่ได้เบิกจ่าย
                                if (!$hasPaid && $hasWork) {
                                    $pendingCount++;
                                    // เพิ่มลงในรายการที่นับแล้ว
                                    $countedPairs[] = $key;
                                }
                            }
                        }
                        
                        \Log::info("Pending TA Payments Count: {$pendingCount}");
                    }
                } catch (\Exception $e) {
                    \Log::error("Error counting pending TA payments: " . $e->getMessage());
                }
            }
            
            $view->with('pendingRequestsCount', $pendingCount);
        });
    }

    private function hasPaidThisMonth($studentId, $courseId, $yearMonth)
    {
        return CompensationTransaction::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('month_year', $yearMonth)
            ->exists();
    }

    private function getActiveSemester()
    {
        // ลองดึงจาก session ก่อน
        $activeSemesterId = session('user_active_semester_id');

        // ถ้าไม่มีใน session ให้ดึงจากฐานข้อมูล
        if (!$activeSemesterId) {
            $setting = DB::table('setting_semesters')->where('key', 'user_active_semester_id')->first();

            if ($setting) {
                $activeSemesterId = $setting->value;
                session(['user_active_semester_id' => $activeSemesterId]);
            }
        }

        // ถ้ายังไม่มีค่า ให้ใช้ semester ล่าสุด
        if (!$activeSemesterId) {
            $semester = Semesters::orderBy('year', 'desc')
                ->orderBy('semesters', 'desc')
                ->first();
        } else {
            $semester = Semesters::find($activeSemesterId);
        }

        return $semester;
    }

    private function hasAttendanceInMonth($studentId, $courseId, $yearMonth)
    {
        list($year, $month) = explode('-', $yearMonth);
        
        // ตรวจสอบการลงเวลาปกติ
        $hasRegularAttendance = \App\Models\Teaching::where('class_id', 'LIKE', $courseId . '%')
            ->whereYear('start_time', $year)
            ->whereMonth('start_time', $month)
            ->whereHas('attendance', function ($query) use ($studentId) {
                $query->where('student_id', $studentId)
                      ->where('approve_status', 'A');
            })
            ->exists();
            
        if ($hasRegularAttendance) {
            return true;
        }
        
        // ตรวจสอบการลงเวลาพิเศษ
        $hasExtraAttendance = \App\Models\ExtraAttendances::where('student_id', $studentId)
            ->where('approve_status', 'A')
            ->whereYear('start_work', $year)
            ->whereMonth('start_work', $month)
            ->exists();
            
        return $hasExtraAttendance;
    }
}