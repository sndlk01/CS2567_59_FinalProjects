<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            // รับข้อมูลจาก Google
            $google_user = Socialite::driver('google')->user();
            $email = $google_user->getEmail();

            // ตรวจสอบโดเมนของอีเมล
            $is_student = str_ends_with($email, '@kkumail.com');
            $is_teacher = str_ends_with($email, '@kku.ac.th');

            // ถ้าไม่ใช่ทั้งอีเมลนักศึกษาหรืออาจารย์ให้แสดงข้อความแจ้งเตือน
            if (!$is_student && !$is_teacher) {
                return redirect()->route('login')->with(['error' => 'คุณสามารถเข้าสู่ระบบได้เฉพาะอีเมล @kkumail.com หรือ @kku.ac.th เท่านั้น']);
            }

            // ค้นหาผู้ใช้จาก Google ID
            $user = User::where('google_id', $google_user->getId())->first();

            // ถ้าไม่พบ ให้ค้นหาผู้ใช้จากอีเมล
            if (!$user) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // สำหรับอาจารย์ ต้องมีข้อมูลในระบบก่อน
                    if ($is_teacher) {
                        return redirect()->route('login')->with(['error' => 'ไม่พบข้อมูลอาจารย์ในระบบ กรุณาติดต่อผู้ดูแลระบบ']);
                    }

                    // สร้างผู้ใช้ใหม่สำหรับนักศึกษา
                    $user = User::create([
                        'name' => $google_user->getName(),
                        'email' => $email,
                        'google_id' => $google_user->getId(),
                        'password' => null,
                        'type' => 0, // 0 สำหรับนักศึกษา (user)
                    ]);
                } else {
                    // อัปเดตข้อมูลผู้ใช้ที่มีอยู่แล้ว
                    $user->update([
                        // 'name' => $google_user->getName(),
                        'google_id' => $google_user->getId(),
                    ]);
                }
            }

            // เข้าสู่ระบบ
            Auth::login($user);

            // จัดการการนำทางหลังจากเข้าสู่ระบบสำเร็จ
            if ($is_teacher) {
                // อาจารย์ไปที่หน้า teacher.home
                return redirect()->route('teacher.home');
            } else {
                // นักศึกษา - ตรวจสอบว่าต้องกรอกข้อมูลเพิ่มเติมหรือไม่
                if (!$user->prefix || !$user->student_id || !$user->card_id || !$user->phone) {
                    return redirect()->route('complete.profile');
                }

                // เปลี่ยนเส้นทางไปยังหน้าหลัก
                return redirect()->intended('home');
            }
        } catch (\Throwable $th) {
            // บันทึกข้อผิดพลาดสำหรับการดีบัก
            Log::error('Google Login Error: ' . $th->getMessage());

            // แสดงข้อความแสดงข้อผิดพลาด
            return redirect()->route('login')->withErrors(['error' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบด้วย Google. โปรดลองใหม่อีกครั้ง']);
        }
    }
}
