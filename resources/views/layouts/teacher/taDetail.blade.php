@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'รายวิชาทั้งหมด')
@section('break2', 'ข้อมูลรายวิชา')
@section('break3', 'ข้อมูลผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>ข้อมูลผู้ช่วยสอน</h4>

                    <!-- ข้อมูลส่วนตัว -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>รหัสนักศึกษา:</strong> {{ $student->student_id }}</p>
                                <p><strong>ชื่อ-นามสกุล:</strong> {{ $student->name }}</p>
                                <p><strong>ระดับการศึกษา:</strong> {{ $student->degree ?? 'ปริญญาตรี' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>อีเมล:</strong> {{ $student->email }}</p>
                                <p><strong>เบอร์โทรศัพท์:</strong> {{ $student->phone ?? 'ไม่ระบุ' }}</p>

                                @if ($student->disbursements) {{-- เปลี่ยนเป็น disbursements --}}
                                    <div class="mt-3">
                                        <p><strong>เอกสารสำคัญ:
                                                @if ($student->disbursements->id)
                                                    @php
                                                        $downloadUrl = route(
                                                            'layout.ta.download-document',
                                                            $student->disbursements->id,
                                                        );
                                                        \Log::debug('Download URL:', ['url' => $downloadUrl]);
                                                    @endphp
                                                    <a href="{{ $downloadUrl }}" class=" color-primary"
                                                        onclick="console.log('Download URL:', '{{ $downloadUrl }}')">
                                                        ดาวน์โหลดเอกสาร click!
                                                    </a>
                                                @endif
                                            </strong></p>

                                    </div>
                                @else
                                    <p class="text-muted">
                                        ยังไม่มีการอัปโหลดเอกสาร
                                        (Debug: {{ var_export($student->disbursements, true) }})
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>


                    <!-- ข้อมูลการลงเวลา -->

                    <!-- ข้อมูลการลงเวลา -->
                    <div class="card-body">
                        <h5 class="card-title">ข้อมูลการลงเวลาการสอน
                            ({{ Carbon\Carbon::parse($semester->start_date)->format('d/m/Y') }} -
                            {{ Carbon\Carbon::parse($semester->end_date)->format('d/m/Y') }})</h5>

                        <form action="{{ route('teacher.approve-attendance') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <form method="GET">
                                    <select name="month" class="form-select" onchange="this.form.submit()">
                                        @foreach($monthsInSemester as $month => $monthName)
                                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                                {{ $monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <div class="d-flex justify-content-end mb-3">
                                    <select name="batch_status" class="form-select w-auto me-2">
                                        <option value="a">อนุมัติทั้งหมด</option>
                                        <option value="w">รอดำเนินการทั้งหมด</option>
                                        <option value="r">ไม่อนุมัติทั้งหมด</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>วันที่</th>
                                            <th>เวลา</th>
                                            <th>หมายเหตุ</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attendances as $attendance)
                                            <tr>
                                                <td>{{ Carbon\Carbon::parse($attendance->created_at)->format('d/m/Y') }}
                                                </td>
                                                <td>{{ Carbon\Carbon::parse($attendance->created_at)->format('H:i') }}</td>
                                                <td>{{ $attendance->note }}</td>
                                                <td>{{ $attendance->status }}</td>
                                                <input type="hidden" name="attendance_ids[]"
                                                    value="{{ $attendance->id }}">
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">ไม่พบข้อมูลการลงเวลา</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>


                    <!-- ปุ่มย้อนกลับ -->
                    <div class="mt-3">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            ย้อนกลับ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
