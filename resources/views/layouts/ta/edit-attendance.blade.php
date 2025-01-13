a<!-- resources/views/layouts/ta/edit-attendance.blade.php -->
@extends('layouts.taLayout')
@section('title', 'แก้ไขการลงเวลา')
@section('break', 'แก้ไขการลงเวลา')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">แก้ไขการลงเวลา</div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST"
                            action="{{ route('attendances.update', ['teaching_id' => $teaching->teaching_id]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="selected_month" value="{{ request('selected_month') }}">

                            <!-- สถานะการเข้าสอน -->
                            <div class="mb-3">
                                <label for="status" class="form-label">สถานะการเข้าสอน</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="เข้าปฏิบัติการสอน"
                                        {{ $teaching->attendance->status === 'เข้าปฏิบัติการสอน' ? 'selected' : '' }}>
                                        เข้าปฏิบัติการสอน
                                    </option>
                                    <option value="ลา" {{ $teaching->attendance->status === 'ลา' ? 'selected' : '' }}>
                                        ลา
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- รายละเอียดการปฏิบัติ -->
                            <div class="mb-3">
                                <label for="note" class="form-label">รายละเอียดการปฏิบัติ</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="4"
                                    required>{{ old('note', $teaching->attendance->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ข้อมูลเพิ่มเติม -->
                            <div class="mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>วันที่:</strong>
                                            {{ \Carbon\Carbon::parse($teaching->start_time)->format('d/m/Y') }}
                                        </p>
                                        <p class="mb-2"><strong>เวลาเริ่ม:</strong>
                                            {{ \Carbon\Carbon::parse($teaching->start_time)->format('H:i') }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>เวลาสิ้นสุด:</strong>
                                            {{ \Carbon\Carbon::parse($teaching->end_time)->format('H:i') }}
                                        </p>
                                        <p class="mb-2"><strong>ระยะเวลา:</strong>
                                            {{ $teaching->duration }} นาที
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ปุ่มดำเนินการ -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('layout.ta.teaching', ['id' => $teaching->class_id]) }}"
                                    class="btn btn-secondary">ยกเลิก</a>
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
