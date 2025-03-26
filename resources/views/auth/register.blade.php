@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center mb-4">{{ __('สมัครสมาชิก') }}</h2>

                        <form method="POST" action="{{ route('register') }}" class="register-form mx-auto">
                            @csrf

                            <div class="row">
                                <!-- Prefix -->
                                <div class="col-md-4 mb-3">
                                    <label class="small mb-1">{{ __('คำนำหน้า') }}</label>
                                    <select class="form-select form-select-sm @error('prefix') is-invalid @enderror" name="prefix" required>
                                        <option value="" disabled {{ old('prefix') ? '' : 'selected' }}>เลือกคำนำหน้า</option>
                                        <option value="นาย" {{ old('prefix') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ old('prefix') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ old('prefix') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                    </select>
                                    @error('prefix')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-md-8 mb-3">
                                    <label class="small mb-1">{{ __('ชื่อ-นามสกุล') }}</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" placeholder="สมชาย ใจดี" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- ID Card -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('รหัสบัตรประชาชน') }}</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('card_id') is-invalid @enderror"
                                        name="card_id" value="{{ old('card_id') }}" placeholder="1234567890123" required>
                                    @error('card_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('หมายเลขโทรศัพท์') }}</label>
                                    <input type="tel"
                                        class="form-control form-control-sm @error('phone') is-invalid @enderror"
                                        name="phone" value="{{ old('phone') }}" placeholder="0812345678" required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Student ID -->
                            <div class="mb-3">
                                <label class="small mb-1">{{ __('รหัสนักศึกษา') }}</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('student_id') is-invalid @enderror"
                                    name="student_id" value="{{ old('student_id') }}" placeholder="64xxxxxxx-x" required>
                                @error('student_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="small mb-1">{{ __('อีเมล') }}</label>
                                <input type="email"
                                    class="form-control form-control-sm @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" placeholder="example@kkumail.com" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('รหัสผ่าน') }}</label>
                                    <input type="password"
                                        class="form-control form-control-sm @error('password') is-invalid @enderror"
                                        name="password" placeholder="อย่างน้อย 8 ตัวอักษร" required>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('ยืนยันรหัสผ่าน') }}</label>
                                    <input type="password" class="form-control form-control-sm" name="password_confirmation"
                                        placeholder="ยืนยันรหัสผ่าน" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    {{ __('บันทึกข้อมูล') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .container {
            font-family: "Noto Sans Thai", sans-serif;
        }

        .card {
            border-radius: 1rem;
            background: #ffffff;
        }

        .form-control-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
            border-radius: 0.4rem;
            border: 1px solid #e2e8f0;
        }

        .form-control-sm:focus {
            border-color: #0061ff;
            box-shadow: 0 0 0 2px rgba(0, 97, 255, 0.1);
        }

        .form-control-sm::placeholder {
            color: #a0aec0;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #0061ff;
            border: none;
            font-size: 0.9rem;
            border-radius: 0.4rem;
            min-width: 120px;
        }

        .btn-primary:hover {
            background-color: #0056e0;
        }

        label {
            color: #4a5568;
            font-weight: 500;
        }

        .register-form {
            max-width: 600px;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
@endsection
