@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'คำร้องการสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>คำร้องการสมัครผู้ช่วยสอน</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        @if ($courseTas->isEmpty())
                            <p>ไม่พบข้อมูลคำร้องการสมัคร</p>
                        @else
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
                                            {{-- <th>การดำเนินการ</th> --}}
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
                                                        <span class="badge bg-success">อนุมัติ</span>
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
                                                {{-- <td>
                                                    <input type="hidden" name="course_ta_ids[]"
                                                        value="{{ $courseTa['course_ta_id'] }}">
                                                    <select name="statuses[]" class="form-select mb-2">
                                                        <option value="a" {{ $status === 'a' ? 'selected' : '' }}>
                                                            อนุมัติ</option>
                                                        <option value="w" {{ $status === 'w' ? 'selected' : '' }}>
                                                            รอดำเนินการ</option>
                                                        <option value="r" {{ $status === 'r' ? 'selected' : '' }}>
                                                            ไม่อนุมัติ</option>
                                                    </select>
                                                    <input type="text" name="comments[]" class="form-control mb-2"
                                                        placeholder="ความคิดเห็น" value="{{ $courseTa['comment'] }}">
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button> --}}
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
