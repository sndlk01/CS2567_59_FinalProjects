@php
    use App\Models\Attendances;
@endphp

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
                                    <!-- In teaching.blade.php -->
                                    <tr>
                                        <td>
                                            @if ($teaching->class_type === 'E')
                                                <span>สอนชดเชย</span>
                                            @elseif ($teaching->is_extra_attendance)
                                                <span>งานเพิ่มเติม</span>
                                                @if ($teaching->class_type === 'L')
                                                    {{-- <span>(ปฏิบัติ)</span> --}}
                                                @elseif ($teaching->class_type === 'C')
                                                    {{-- <span>(บรรยาย)</span> --}}
                                                @endif
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
                                            @if ($teaching->attendance)
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @elseif ($teaching->is_extra_attendance)
                                                <span class="badge bg-success">บันทึกแล้ว</span>
                                            @else
                                                <span class="badge bg-secondary">รอการลงเวลา</span>
                                            @endif
                                        </td>
                                        <td>{{ $teaching->attendance->note ?? '-' }}</td>
                                        <td>
                                            @if ($teaching->attendance || $teaching->is_extra_attendance)
                                                <div class="btn-group">
                                                    @php
                                                        $isApproved = false;
                                                        if ($teaching->attendance) {
                                                            if ($teaching->is_extra_attendance) {
                                                                $isApproved =
                                                                    isset($teaching->attendance->approve_status) &&
                                                                    $teaching->attendance->approve_status === 'a';
                                                            } else {
                                                                $isApproved =
                                                                    isset($teaching->attendance->approve_status) &&
                                                                    $teaching->attendance->approve_status === 'a';
                                                            }
                                                        }
                                                    @endphp

                                                    @if (!$isApproved)
                                                        @if ($teaching->is_extra_attendance)
                                                            <a href="{{ route('extra-attendance.edit', ['id' => substr($teaching->id, 6), 'selected_month' => $selectedMonth]) }}"
                                                                class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteExtraModal{{ substr($teaching->id, 6) }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        @else
                                                            <a href="{{ route('attendances.edit', ['teaching_id' => $teaching->id, 'selected_month' => $selectedMonth]) }}"
                                                                class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteModal{{ $teaching->id }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            @else
                                                @php
                                                    // ตรวจสอบว่าเดือนนี้ได้รับการอนุมัติแล้วหรือไม่
                                                    $selectedDate = \Carbon\Carbon::parse($teaching->start_time);
                                                    $isMonthApproved = Attendances::where(
                                                        'student_id',
                                                        Auth::user()->student->id,
                                                    )
                                                        ->whereYear('created_at', $selectedDate->year)
                                                        ->whereMonth('created_at', $selectedDate->month)
                                                        ->where('approve_status', 'a')
                                                        ->exists();
                                                @endphp

                                                @if (
                                                    !$isMonthApproved &&
                                                        (!$teaching->attendance ||
                                                            (isset($teaching->attendance->approve_status) && $teaching->attendance->approve_status !== 'a')))
                                                    <a href="{{ route('attendances.form', ['teaching_id' => $teaching->id, 'selected_month' => $selectedMonth]) }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        ลงเวลา
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-outline-secondary btn-sm disabled"
                                                        aria-disabled="true">
                                                        ลงเวลา
                                                    </a>
                                                @endif
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

    <!-- Add these modal definitions -->
    @foreach ($teachings as $teaching)
        @if ($teaching->is_extra_attendance)
            <!-- Delete Extra Attendance Modal -->
            <div class="modal fade" id="deleteExtraModal{{ substr($teaching->id, 6) }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">ยืนยันการลบ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            คุณต้องการลบการลงเวลาเพิ่มเติมนี้ใช่หรือไม่?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <form action="{{ route('extra-attendance.delete', substr($teaching->id, 6)) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="selected_month" value="{{ $selectedMonth }}">
                                <button type="submit" class="btn btn-danger">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($teaching->attendance)
            <!-- Delete Regular Attendance Modal -->
            <div class="modal fade" id="deleteModal{{ $teaching->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">ยืนยันการลบ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            คุณต้องการลบการลงเวลานี้ใช่หรือไม่?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <form action="{{ route('attendances.delete', $teaching->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="selected_month" value="{{ $selectedMonth }}">
                                <button type="submit" class="btn btn-danger">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

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
                            <input type="datetime-local" class="form-control" id="start_work" name="start_work"
                                required>
                        </div>

                        <!-- ประเภทรายวิชาที่ปฏิบัติ -->
                        <div class="mb-3">
                            <label for="class_type" class="form-label">ประเภทรายวิชาที่ปฏิบัติ</label>
                            <select class="form-select" id="class_type" name="class_type" required>
                                <option value="">เลือกประเภทรายวิชา</option>
                                <option value="L">ปฏิบัติ</option>
                                <option value="C">บรรยาย</option>
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
                        <input type="hidden" name="selected_month" value="{{ $selectedMonth }}">
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
