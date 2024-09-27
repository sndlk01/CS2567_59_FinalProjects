<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $input = $request->all();

        // เปลี่ยนการ validate input ให้อ่านได้ทั้ง email หรือ student_id
        $this->validate($request, [
            'email_or_student_id' => 'required',
            'password' => 'required',
        ]);

        // ตรวจสอบว่า input ที่กรอกเข้ามาเป็น email หรือ student_id
        $fieldType = filter_var($input['email_or_student_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'student_id';

        // Attempt login โดยใช้ฟิลด์ตามประเภทที่ตรวจพบ (email หรือ student_id)
        if (auth()->attempt([$fieldType => $input['email_or_student_id'], 'password' => $input['password']])) {
            // ตรวจสอบประเภทผู้ใช้งาน และเปลี่ยนเส้นทางตาม role
            if (auth()->user()->type == 'admin') {
                return redirect()->route('admin.home');
            } else if (auth()->user()->type == 'teacher') {
                return redirect()->route('teacher.home');
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('login')->with('error', 'Email/Student ID and Password are incorrect.');
        }
    }
}
