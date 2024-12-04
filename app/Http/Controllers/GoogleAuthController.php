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

            // ตรวจสอบว่าอีเมลเป็น @kkumail.com หรือไม่
            // $email = $google_user->getEmail();
            // if (!str_ends_with($email, '@kkumail.com')) {
            //     return redirect()->route('login')->with(['error' => 'คุณสามารถเข้าสู่ระบบได้เฉพาะอีเมล @kkumail.com เท่านั้น']);
            // }

            // ค้นหาผู้ใช้จาก Google ID
            $user = User::where('google_id', $google_user->getId())->first();

            // ถ้าไม่พบ ให้ค้นหาผู้ใช้จากอีเมล
            // ถ้าไม่พบ ให้ค้นหาผู้ใช้จากอีเมล
            if (!$user) {
                $user = User::where('email', $google_user->getEmail())->first();

                if (!$user) {
                    // สร้างผู้ใช้ใหม่
                    $user = User::create([
                        'name' => $google_user->getName(), // ใช้ชื่อเต็มจาก Google
                        'email' => $google_user->getEmail(),
                        'google_id' => $google_user->getId(),
                        'password' => null, // ไม่จำเป็นต้องมีรหัสผ่าน
                    ]);
                } else {
                    // ถ้าผู้ใช้งานมีอยู่แล้วและต้องการอัปเดตข้อมูล
                    $user->update([
                        'name' => $google_user->getName(), // อัปเดตชื่อเต็ม
                        'google_id' => $google_user->getId(), // อัปเดต Google ID
                        'email' => $google_user->getEmail(), // อัปเดตอีเมล หากจำเป็น
                    ]);
                }
            }


            // เข้าสู่ระบบ
            Auth::login($user);

            // Check if the user needs to complete their profile
            if (!$user->prefix || !$user->student_id || !$user->card_id || !$user->phone) {
                return redirect()->route('complete.profile');
            }

            // เปลี่ยนเส้นทางไปยังหน้าที่ตั้งใจไว้
            return redirect()->intended('home');
        } catch (\Throwable $th) {
            // บันทึกข้อผิดพลาดสำหรับการดีบัก
            Log::error('Google Login Error: ' . $th->getMessage());

            // แสดงข้อความแสดงข้อผิดพลาด
            return redirect()->route('login')->withErrors(['error' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบด้วย Google. โปรดลองใหม่อีกครั้ง']);
        }
    }
}
