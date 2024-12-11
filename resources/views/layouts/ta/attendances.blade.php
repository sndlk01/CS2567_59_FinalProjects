@extends('layouts.taLayout')

@section('title', 'attendance')
@section('break', 'ลงเวลาการสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container mt-4">
                        <h4>ลงเวลาการสอน</h4>

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="card mb-4">
                            <div class="card-body">
                                <h6>การเข้าสอน</h6>
                                <form action="{{ route('attendances.submit', $teaching->teaching_id) }}" method="POST">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">สถานะการเข้าสอน</label>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('status') is-invalid @enderror"
                                                id="status1" name="status" value="เข้าปฏิบัติการสอน"
                                                {{ old('status') == 'เข้าปฏิบัติการสอน' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status1">เข้าปฏิบัติการสอน</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('status') is-invalid @enderror"
                                                id="status2" name="status" value="ลา"
                                                {{ old('status') == 'ลา' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status2">ลา</label>
                                        </div>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="note" class="form-label">งานที่ปฏิบัติ</label>
                                        <input type="text" class="form-control @error('note') is-invalid @enderror"
                                            id="note" name="note" value="{{ old('note') }}"
                                            placeholder="ระบุงานที่ปฏิบัติ">
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                                    <a href="{{ route('layout.ta.teaching', ['id' => $teaching->class_id]) }}"
                                        class="btn btn-secondary">ยกเลิก</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
