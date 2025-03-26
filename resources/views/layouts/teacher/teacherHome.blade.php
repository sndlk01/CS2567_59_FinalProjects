@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'คำร้องการสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>คำร้องการสมัครผู้ช่วยสอน</h4>

                    @if (isset($currentSemester))
                        <div class="alert alert-info">
                            กำลังแสดงข้อมูลภาคการศึกษา: {{ $currentSemester->year }}/{{ $currentSemester->semesters }}
                            ({{ \Carbon\Carbon::parse($currentSemester->start_date)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($currentSemester->end_date)->format('d/m/Y') }})
                        </div>
                    @endif

                    <div class="container shadow-lg bg-body rounded p-5">
                        @if ($courseTas->isEmpty())
                            <p>ไม่พบข้อมูลคำร้องการสมัครในภาคการศึกษานี้</p>
                        @else
                            @php
                                // ตรวจสอบว่ามีรายการที่ยังไม่ได้อนุมัติหรือไม่
                                $hasEditableRequests = false;
                                foreach ($courseTas as $courseTa) {
                                    $status = strtolower($courseTa['status'] ?? 'w');
                                    if ($status !== 'a') {
                                        $hasEditableRequests = true;
                                        break;
                                    }
                                }
                            @endphp
                            
                            <form action="{{ route('teacher.home') }}" method="POST">
                                @csrf
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ลำดับ</th>
                                            <th>รหัสนักศึกษา</th>
                                            <th>ชื่อ-นามสกุล</th>
                                            <th>รายวิชา</th>
                                            <th>สถานะการสมัคร</th>
                                            <th>วันที่อนุมัติ</th>
                                            <th>การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($courseTas as $index => $courseTa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $courseTa['student_id'] }}</td>
                                                <td>{{ $courseTa['student_name'] }}</td>
                                                <td>{{ $courseTa['course'] }}</td>
                                                <td>
                                                    @php
                                                        $status = strtolower($courseTa['status'] ?? 'w');
                                                    @endphp
                                                    @if ($status === 'w')
                                                        <span class="badge bg-warning">รอดำเนินการ</span>
                                                    @elseif ($status === 'r')
                                                        <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                    @elseif ($status === 'a')
                                                        <span class="badge bg-success">อนุมัติแล้ว</span>
                                                    @else
                                                        <span class="badge bg-secondary">ไม่ระบุ</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($courseTa['approved_at'])
                                                        {{ \Carbon\Carbon::parse($courseTa['approved_at'])->format('d-m-Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="hidden" name="course_ta_ids[]" value="{{ $courseTa['course_ta_id'] }}">
                                                    @if ($status === 'a')
                                                        <input type="hidden" name="statuses[]" value="a">
                                                        <input type="hidden" name="comments[]" value="{{ $courseTa['comment'] }}">
                                                    @else
                                                        <select name="statuses[]" class="form-select mb-2">
                                                            <option value="a" {{ $status === 'a' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                                            <option value="w" {{ $status === 'w' ? 'selected' : '' }}>รอดำเนินการ</option>
                                                            <option value="r" {{ $status === 'r' ? 'selected' : '' }}>ไม่อนุมัติ</option>
                                                        </select>
                                                        <input type="text" name="comments[]" class="form-control mb-2" placeholder="ความคิดเห็น" value="{{ $courseTa['comment'] }}">
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if ($hasEditableRequests)
                                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                                @else
                                    <div class="mt-3 text-success">
                                        <i class="fas fa-info-circle me-2"></i> ทุกคำร้องได้รับการอนุมัติเรียบร้อยแล้ว
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection