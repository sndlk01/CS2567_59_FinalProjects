<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Teachers;
use App\Models\CourseTas;
use App\Models\Semesters;

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
}
