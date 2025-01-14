@extends('layouts.adminLayout')

@section('title', 'ข้อมูลผู้ช่วยสอนรายบุคคล')

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
                    <!-- ส่วนของฟิลเตอร์ -->
                    <div class="card-body">
                        @if (isset($semester) && $semester)
                            <h5 class="card-title">ข้อมูลการลงเวลาการสอน
                                ({{ Carbon\Carbon::parse($semester->start_date)->format('d/m/Y') }} -
                                {{ Carbon\Carbon::parse($semester->end_date)->format('d/m/Y') }})
                            </h5>
                        @else
                            <h5 class="card-title">ข้อมูลการลงเวลาการสอน</h5>
                        @endif

                        <div class="mb-3">
                            <form method="GET" class="d-flex align-items-center gap-3">
                                <!-- ตัวเลือกประเภทการลงเวลา -->
                                <div class="d-flex align-items-center">
                                    <label for="type" class="me-2">ประเภท:</label>
                                    <select name="type" id="type" class="form-select" style="width: 150px;">
                                        <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>
                                            ทั้งหมด</option>
                                        <option value="regular" {{ request('type') === 'regular' ? 'selected' : '' }}>ปกติ
                                        </option>
                                        <option value="special" {{ request('type') === 'special' ? 'selected' : '' }}>พิเศษ
                                        </option>
                                    </select>
                                </div>

                                <!-- ตัวเลือกเดือน -->
                                <div class="d-flex align-items-center">
                                    <label for="month" class="me-2">เดือน:</label>
                                    <select name="month" id="month" class="form-select" style="width: 200px;">
                                        @foreach ($monthsInSemester as $yearMonth => $monthName)
                                            <option value="{{ $yearMonth }}"
                                                {{ $selectedYearMonth == $yearMonth ? 'selected' : '' }}>
                                                {{ $monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">แสดงข้อมูล</button>
                            </form>
                        </div>

                        <!-- แสดงข้อมูลแยกตาม Section -->
                        <!-- แสดงข้อมูลแยกตาม Section -->
                        @forelse($attendancesBySection as $section => $attendances)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Section {{ $section }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ประเภท</th>
                                                    <th>รูปแบบ</th>
                                                    <th>วันที่</th>
                                                    <th>เวลาสอน</th>
                                                    <th>ชั่วโมงการสอน</th>
                                                    <th>อาจารย์ประจำวิชา</th>
                                                    <th>สถานะ</th>
                                                    <th>รายละเอียด</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($attendances as $attendance)
                                                    <tr>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                <span class="badge bg-primary">ปกติ</span>
                                                            @else
                                                                <span class="badge bg-info">พิเศษ</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                @if ($attendance['data']->class_type === 'L')
                                                                    <span class="badge bg-warning">LAB</span>
                                                                @else
                                                                    <span class="badge bg-secondary">LECTURE</span>
                                                                @endif
                                                            @else
                                                                @if ($attendance['data']->class_type === 'L')
                                                                    <span class="badge bg-warning">LAB</span>
                                                                @else
                                                                    <span class="badge bg-secondary">LECTURE</span>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                {{ \Carbon\Carbon::parse($attendance['data']->start_time)->format('d-m-Y') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($attendance['data']->start_work)->format('d-m-Y') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                {{ \Carbon\Carbon::parse($attendance['data']->start_time)->format('H:i') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($attendance['data']->end_time)->format('H:i') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($attendance['data']->start_work)->format('H:i') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                @php
                                                                    $start = \Carbon\Carbon::parse(
                                                                        $attendance['data']->start_time,
                                                                    );
                                                                    $end = \Carbon\Carbon::parse(
                                                                        $attendance['data']->end_time,
                                                                    );
                                                                    $durationInHours = $end->diffInMinutes($start) / 60;
                                                                @endphp
                                                                {{ number_format($durationInHours, 2) }} ชั่วโมง
                                                            @else
                                                                {{ number_format($attendance['data']->duration / 60, 2) }}
                                                                ชั่วโมง
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                {{ $attendance['data']->teacher->position ?? '' }}
                                                                {{ $attendance['data']->teacher->degree ?? '' }}
                                                                {{ $attendance['data']->teacher->name ?? '' }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">อนุมัติแล้ว</span>
                                                        </td>
                                                        <td>
                                                            @if ($attendance['type'] === 'regular')
                                                                {{ $attendance['data']->attendance->note ?? '-' }}
                                                            @else
                                                                {{ $attendance['data']->detail ?? '-' }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                ไม่พบข้อมูลการลงเวลาในช่วงเวลาที่เลือก
                            </div>
                        @endforelse

                        <div class="mt-3">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                ย้อนกลับ
                            </a>
                        </div>
                    </div>
                    
                </div>

                <!-- ปุ่มย้อนกลับ -->

            </div>
        </div>
    </div>

@endsection
