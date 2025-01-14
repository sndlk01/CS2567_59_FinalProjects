<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'prefix' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'card_id' => ['required', 'string', 'max:13', 'unique:users'],
            'phone' => ['required', 'string', 'max:10', 'unique:users'],
            'student_id' => ['required', 'string', 'max:11', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            // ข้อความ error ภาษาไทย
            'required' => 'กรุณากรอก:attribute',
            'max' => ':attribute ต้องไม่เกิน :max ตัวอักษร',
            'min' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร',
            'unique' => ':attribute นี้ถูกใช้งานแล้ว',
            'email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'confirmed' => 'การยืนยันรหัสผ่านไม่ตรงกัน',

            // กำหนดชื่อฟิลด์ภาษาไทย
            'attributes' => [
                'prefix' => 'คำนำหน้า',
                'name' => 'ชื่อ-นามสกุล',
                'card_id' => 'รหัสบัตรประชาชน',
                'phone' => 'หมายเลขโทรศัพท์',
                'student_id' => 'รหัสนักศึกษา',
                'email' => 'อีเมล',
                'password' => 'รหัสผ่าน',
            ]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'prefix' => $data['prefix'],
            'name' => $data['name'],
            'card_id' => $data['card_id'],
            'phone' => $data['phone'],
            'student_id' => $data['student_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => '0',
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // Create user and log them in
        $user = $this->create($request->all());
        Auth::login($user);

        // Redirect to intended location after registration
        return redirect()->intended('home')->with('success', 'Registration complete and user logged in!');
    }
}
