@extends('layouts.taLayout')

@section('title', 'teaching')
@section('break', 'ตารางรายวิชา')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">

                    <!-- Extra Attendance Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#extraAttendanceModal">
                            <i class="bi bi-plus-circle me-2"></i>ลงเวลาเพิ่มเติม
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>กลุ่ม</th>
                                    <th>เวลาเริ่มเรียน</th>
                                    <th>เวลาเลิกเรียน</th>
                                    <th>เวลาที่สอน(นาที)</th>
                                    <th>อาจารย์ประจำวิชา</th>
                                    <th>การปฏิบัติงาน</th>
                                    <th>รายละเอียด</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $previousClassTitle = null;
                                @endphp
                                @forelse ($teachings as $teaching)
                                    @if ($previousClassTitle != $teaching->class_id->title)
                                        <tr>
                                            <td colspan="8" class="fw-bold bg-light">
                                                {{ $teaching->class_id->title }}
                                            </td>
                                        </tr>
                                        @php
                                            $previousClassTitle = $teaching->class_id->title;
                                        @endphp
                                    @endif
                                    <tr>
                                        <td>
                                            @if ($teaching->class_type === 'E')
                                                <span>สอนชดเชย</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->start_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->end_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ $teaching->duration }}</td>
                                        <td>
                                            {{ $teaching->teacher_id->position }}
                                            {{ $teaching->teacher_id->degree }}
                                            {{ $teaching->teacher_id->name }}
                                        </td>
                                        <td>
                                            @if ($teaching->attendance && $teaching->attendance->status === 'เข้าปฏิบัติการสอน')
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @elseif ($teaching->attendance && $teaching->attendance->status === 'ลา')
                                                <span class="badge bg-warning">ลา</span>
                                            @else
                                                <span class="badge bg-secondary">รอการลงเวลา</span>
                                            @endif
                                        </td>
                                        <td>{{ $teaching->attendance->note ?? '-' }}</td>
                                        <td>
                                            @if ($teaching->attendance)
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    ลงเวลาแล้ว
                                                </button>
                                            @else
                                                <a href="{{ route('attendances.form', ['teaching_id' => $teaching->id, 'selected_month' => $selectedMonth]) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    ลงเวลา
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">ไม่พบข้อมูลการสอน</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extra Attendance Modal -->
    <div class="modal fade" id="extraAttendanceModal" tabindex="-1" aria-labelledby="extraAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extraAttendanceModalLabel">ลงเวลาเพิ่มเติม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('extra-attendance.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- วันที่ -->
                        <div class="mb-3">
                            <label for="start_work" class="form-label">วันที่ปฏิบัติงาน</label>
                            <input type="datetime-local" class="form-control" id="start_work" name="start_work" required>
                        </div>

                        <!-- ประเภทรายวิชาที่ปฏิบัติ -->
                        <div class="mb-3">
                            <label for="class_type" class="form-label">ประเภทรายวิชาที่ปฏิบัติ</label>
                            <select class="form-select" id="class_type" name="class_type" required>
                                <option value="">เลือกประเภทรายวิชา</option>
                                <option value="L">ปฏิบัติ</option>
                                <option value="C">บรรยาย</option>
                                {{-- <option value="P">Project</option> --}}
                            </select>
                        </div>

                        <!-- รายละเอียดการปฏิบัติงาน -->
                        <div class="mb-3">
                            <label for="detail" class="form-label">รายละเอียดการปฏิบัติงาน</label>
                            <textarea class="form-control" id="detail" name="detail" rows="3" required></textarea>
                        </div>

                        <!-- ระยะเวลาการปฏิบัติ -->
                        <div class="mb-3">
                            <label for="duration" class="form-label">ระยะเวลาการปฏิบัติ (นาที)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="1"
                                required>
                        </div>

                        <input type="hidden" name="student_id" value="{{ Auth::user()->student->id }}">
                        <input type="hidden" name="class_id" value="{{ request()->route('id') }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
