@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white">
                        <h3 class="text-center mb-0">กรอกข้อมูลโปรไฟล์ของคุณ</h3>
                    </div>
                    <div class="card-body px-5 py-4">
                        <form method="POST" action="{{ route('save.profile') }}">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="student_id" class="form-label fw-bold">รหัสนักศึกษา</label>
                                    <input type="text" class="form-control @error('student_id') is-invalid @enderror"
                                        id="student_id" name="student_id" value="{{ old('student_id', $user->student_id) }}"
                                        placeholder="กรอกเลขรหัสนักศึกษา">
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="prefix" class="form-label fw-bold">คำนำหน้า</label>
                                    <select class="form-select @error('prefix') is-invalid @enderror" id="prefix"
                                        name="prefix">
                                        <option value="">เลือกคำนำหน้า</option>
                                        <option value="นาย"
                                            {{ old('prefix', $user->prefix) == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นางสาว"
                                            {{ old('prefix', $user->prefix) == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                    </select>
                                    @error('prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">ชื่อ</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}"
                                        placeholder="กรอกชื่อ">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_id" class="form-label fw-bold">รหัสบัตรประชาชน</label>
                                    <input type="text" class="form-control @error('card_id') is-invalid @enderror"
                                        id="card_id" name="card_id" value="{{ old('card_id', $user->card_id) }}"
                                        placeholder="กรอก 13 หลัก">
                                    @error('card_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-bold">เบอร์โทรศัพท์</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                        placeholder="เช่น 0812345678">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-bold">รหัสผ่าน</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="ตั้งรหัสผ่าน">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-bold">ยืนยันรหัสผ่าน</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="กรอกรหัสผ่านอีกครั้ง">
                                </div>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-dark px-5 py-2">
                                    <i class="bi bi-save"></i> บันทึกข้อมูล
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
