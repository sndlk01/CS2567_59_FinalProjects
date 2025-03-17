@extends('layouts.teacherLayout')

@section('title', 'เปลี่ยนรหัสผ่าน')
@section('break', 'เปลี่ยนรหัสผ่าน')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">เปลี่ยนรหัสผ่าน</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('teacher.update-password') }}">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="current_password">รหัสผ่านปัจจุบัน</label>
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror" required>
                                        @error('current_password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="password">รหัสผ่านใหม่</label>
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror" required>
                                        <small class="form-text text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8
                                            ตัวอักษร</small>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
