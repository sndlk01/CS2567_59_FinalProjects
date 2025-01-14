<!-- resources/views/layouts/ta/edit-extra-attendance.blade.php -->
@extends('layouts.taLayout')
@section('title', 'แก้ไขการลงเวลาเพิ่มเติม')
@section('break', 'แก้ไขการลงเวลาเพิ่มเติม')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">แก้ไขการลงเวลาเพิ่มเติม</div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST"
                            action="{{ route('extra-attendance.update', ['id' => $extraAttendance->id]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="selected_month" value="{{ request('selected_month') }}">

                            <!-- วันที่และเวลา -->
                            <div class="mb-3">
                                <label for="start_work" class="form-label">วันที่ปฏิบัติงาน</label>
                                <input type="datetime-local" class="form-control @error('start_work') is-invalid @enderror"
                                    id="start_work" name="start_work"
                                    value="{{ old('start_work', \Carbon\Carbon::parse($extraAttendance->start_work)->format('Y-m-d\TH:i')) }}"
                                    required>
                                @error('start_work')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ประเภทรายวิชา -->
                            <div class="mb-3">
                                <label for="class_type" class="form-label">ประเภทรายวิชาที่ปฏิบัติ</label>
                                <select class="form-select @error('class_type') is-invalid @enderror" id="class_type"
                                    name="class_type" required>
                                    <option value="">เลือกประเภทรายวิชา</option>
                                    <option value="L"
                                        {{ old('class_type', $extraAttendance->class_type) === 'L' ? 'selected' : '' }}>
                                        ปฏิบัติ
                                    </option>
                                    <option value="C"
                                        {{ old('class_type', $extraAttendance->class_type) === 'C' ? 'selected' : '' }}>
                                        บรรยาย
                                    </option>
                                </select>
                                @error('class_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- รายละเอียดการปฏิบัติ -->
                            <div class="mb-3">
                                <label for="detail" class="form-label">รายละเอียดการปฏิบัติงาน</label>
                                <textarea class="form-control @error('detail') is-invalid @enderror" id="detail" name="detail" rows="4"
                                    required>{{ old('detail', $extraAttendance->detail) }}</textarea>
                                @error('detail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ระยะเวลา -->
                            <div class="mb-4">
                                <label for="duration" class="form-label">ระยะเวลาการปฏิบัติ (นาที)</label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror"
                                    id="duration" name="duration"
                                    value="{{ old('duration', $extraAttendance->duration) }}" min="1" required>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ปุ่มดำเนินการ -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('layout.ta.teaching', ['id' => $extraAttendance->class_id]) }}"
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
