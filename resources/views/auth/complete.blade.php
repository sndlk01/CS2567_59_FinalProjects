@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center mb-4">{{ __('สมัครสมาชิก') }}</h2>


                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('save.profile') }}" class="register-form mx-auto">
                            @csrf

                            <!-- Student ID -->
                            <div class="mb-3">
                                <label class="small mb-1">{{ __('รหัสนักศึกษา') }}</label>
                                <input type="text" 
                                    class="form-control form-control-sm @error('student_id') is-invalid @enderror"
                                    name="student_id" 
                                    value="{{ old('student_id', $user->student_id) }}" 
                                    placeholder="กรอกเลขรหัสนักศึกษา" 
                                    required>
                                @error('student_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Prefix -->
                                <div class="col-md-4 mb-3">
                                    <label class="small mb-1">{{ __('คำนำหน้า') }}</label>
                                    <input type="text" 
                                        class="form-control form-control-sm @error('prefix') is-invalid @enderror"
                                        name="prefix" 
                                        value="{{ old('prefix', $user->prefix) }}" 
                                        placeholder="นาย/นาง/นางสาว"
                                        required>
                                    @error('prefix')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-md-8 mb-3">
                                    <label class="small mb-1">{{ __('ชื่อ-นามสกุล') }}</label>
                                    <input type="text" 
                                        class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        name="name" 
                                        value="{{ old('name', $user->name) }}" 
                                        placeholder="กรอกชื่อ-นามสกุล" 
                                        required>
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
                                        name="card_id" 
                                        value="{{ old('card_id', $user->card_id) }}" 
                                        placeholder="กรอก 13 หลัก" 
                                        required>
                                    @error('card_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('หมายเลขโทรศัพท์') }}</label>
                                    <input type="tel" 
                                        class="form-control form-control-sm @error('phone') is-invalid @enderror"
                                        name="phone" 
                                        value="{{ old('phone', $user->phone) }}" 
                                        placeholder="เช่น 0812345678" 
                                        required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('รหัสผ่าน') }}</label>
                                    <input type="password" 
                                        class="form-control form-control-sm @error('password') is-invalid @enderror"
                                        name="password" 
                                        placeholder="ตั้งรหัสผ่าน" 
                                        required>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="small mb-1">{{ __('ยืนยันรหัสผ่าน') }}</label>
                                    <input type="password" 
                                        class="form-control form-control-sm"
                                        name="password_confirmation" 
                                        placeholder="กรอกรหัสผ่านอีกครั้ง" 
                                        required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-save"></i> บันทึกข้อมูล
                                </button>
                                <p class="text-muted small mt-3 pt-3 border-top">
                                    กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก
                                </p>
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

        .form-control-sm, .form-select-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
            border-radius: 0.4rem;
            border: 1px solid #e2e8f0;
        }

        .form-control-sm:focus, .form-select-sm:focus {
            border-color: #0061ff;
            box-shadow: 0 0 0 2px rgba(0, 97, 255, 0.1);
        }

        .form-control-sm::placeholder, .form-select-sm {
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

        .alert {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
@endsection