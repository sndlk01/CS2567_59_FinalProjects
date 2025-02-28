@extends('layouts.adminLayout')

@section('title', 'Announce Management')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    {{-- <div class="card bg-primary text-white p-3"> --}}
                    <h4>รายชื่อผู้สมัครเป็นผู้ช่วยสอน</h4>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <td>ลำดับ</td>
                                <td>รหัสนักศึกษา</td>
                                <td>ชื่อ-นามสกุล</td>
                                <td>รายวิชาที่สมัคร</td>
                                <td>วันที่สมัคร</td>
                                <td>สถานะการสมัคร</td>
                                <td>วันที่อนุมัติ</td>
                                <td>ความคิดเห็น</td>
                            </tr>
                            @forelse ($requests as $index => $request)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $request['student_id'] }}</td>
                                            <td>{{ $request['full_name'] }}</td>
                                            <td>{{ $request['course'] }}</td>
                                            <td>{{ $request['applied_at']->format('d-m-Y') }}</td>
                                            <td>
                                                @php
                                                    $status = strtolower($request['status']);
                                                @endphp
                                                @if ($status === 'w')
                                                    <span class="badge bg-warning">รอดำเนินการ</span>
                                                @elseif ($status === 'r')
                                                    <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                @elseif ($status === 'a')
                                                    <span class="badge bg-success">อนุมัติ</span>
                                                @elseif ($status === 'p')
                                                    <span class="badge bg-info">กำลังพิจารณา</span>
                                                @else
                                                    <span class="badge bg-secondary">ไม่ระบุ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($request['approved_at'])
                                                    @php
                                                        $approvedAt = $request['approved_at'];
                                                        if (is_string($approvedAt)) {
                                                            $approvedAt = \Carbon\Carbon::parse($approvedAt);
                                                        }
                                                    @endphp
                                                    {{ $approvedAt->format('d-m-Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $request['comment'] ?? 'ไม่มีความคิดเห็น' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">ไม่พบข้อมูลคำร้องการสมัคร</td>
                                        </tr>
                                    @endforelse
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
