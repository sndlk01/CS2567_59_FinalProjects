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
                                <p><strong>ระดับการศึกษา:</strong>
                                    @switch($student->degree_level)
                                        @case('bachelor')
                                            ปริญญาตรี
                                        @break

                                        @case('master')
                                            ปริญญาโท
                                        @break

                                        @case('doctoral')
                                            ปริญญาเอก
                                        @break

                                        @default
                                            {{ $student->degree_level ?? 'ไม่ระบุ' }}
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>อีเมล:</strong> {{ $student->email }}</p>
                                <p><strong>เบอร์โทรศัพท์:</strong> {{ $student->phone ?? 'ไม่ระบุ' }}</p>

                                @if ($student->disbursements)
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

                        <!-- ข้อมูลโครงการปกติ -->
                        <h5 class="mt-4 mb-3">ข้อมูลโครงการปกติ</h5>
                        @php
                            $regularAttendances = $attendancesBySection
                                ->map(function ($sectionAttendances) {
                                    return $sectionAttendances->filter(function ($attendance) {
                                        if ($attendance['type'] === 'regular') {
                                            return $attendance['data']->class->major->major_type !== 'S';
                                        } else {
                                            return $attendance['data']->classes->major->major_type !== 'S';
                                        }
                                    });
                                })
                                ->filter(function ($sectionAttendances) {
                                    return $sectionAttendances->isNotEmpty();
                                });
                        @endphp

                        @forelse($regularAttendances as $section => $attendances)
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
                                                            <span class="badge bg-primary">โครงการปกติ</span>
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
                                ไม่พบข้อมูลการลงเวลาโครงการปกติในช่วงเวลาที่เลือก
                            </div>
                        @endforelse

                        <!-- ข้อมูลโครงการพิเศษ -->
                        <h5 class="mt-4 mb-3">ข้อมูลโครงการพิเศษ</h5>
                        @php
                            $specialAttendances = $attendancesBySection
                                ->map(function ($sectionAttendances) {
                                    return $sectionAttendances->filter(function ($attendance) {
                                        if ($attendance['type'] === 'regular') {
                                            return $attendance['data']->class->major->major_type === 'S';
                                        } else {
                                            return $attendance['data']->classes->major->major_type === 'S';
                                        }
                                    });
                                })
                                ->filter(function ($sectionAttendances) {
                                    return $sectionAttendances->isNotEmpty();
                                });
                        @endphp

                        @forelse($specialAttendances as $section => $attendances)
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
                                                            <span class="badge bg-warning">โครงการพิเศษ</span>
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
                                ไม่พบข้อมูลการลงเวลาโครงการพิเศษในช่วงเวลาที่เลือก
                            </div>
                        @endforelse

                        <!-- สรุปค่าตอบแทน -->
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">สรุปค่าตอบแทน</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ประเภทค่าตอบแทน</th>
                                                <th class="text-center">จำนวนชั่วโมง</th>
                                                <th class="text-center">อัตราค่าตอบแทน (บาท)</th>
                                                <th class="text-end">จำนวนเงิน (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>คาบบรรยาย ภาคปกติ</td>
                                                <td class="text-center">
                                                    {{ number_format($compensation['regularLectureHours'], 2) }}</td>
                                                <td class="text-center text-primary fw-bold">
                                                    {{ number_format($compensation['rates']['regularLecture'], 2) }}</td>
                                                <td class="text-end">
                                                    {{ number_format($compensation['regularLecturePay'], 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>คาบปฏิบัติการ ภาคปกติ</td>
                                                <td class="text-center">
                                                    {{ number_format($compensation['regularLabHours'], 2) }}</td>
                                                <td class="text-center text-primary fw-bold">
                                                    {{ number_format($compensation['rates']['regularLab'], 2) }}</td>
                                                <td class="text-end">{{ number_format($compensation['regularLabPay'], 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>คาบบรรยาย ภาคพิเศษ</td>
                                                <td class="text-center">
                                                    {{ number_format($compensation['specialLectureHours'], 2) }}</td>
                                                <td class="text-center text-primary fw-bold">
                                                    {{ number_format($compensation['rates']['specialLecture'], 2) }}</td>
                                                <td class="text-end">
                                                    {{ number_format($compensation['specialLecturePay'], 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>คาบปฏิบัติการ ภาคการพิเศษ</td>
                                                <td class="text-center">
                                                    {{ number_format($compensation['specialLabHours'], 2) }}</td>
                                                <td class="text-center text-primary fw-bold">
                                                    {{ number_format($compensation['rates']['specialLab'], 2) }}</td>
                                                <td class="text-end">{{ number_format($compensation['specialLabPay'], 2) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="2" class="text-end">จำนวนชั่วโมงรวม</td>
                                                <td class="text-center">
                                                    {{ number_format($compensation['regularHours'] + $compensation['specialHours'], 2) }}
                                                    ชั่วโมง</td>
                                                <td class="text-end">{{ number_format($compensation['totalPay'], 2) }} บาท
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('admin.compensation-rates.index') }}" class="btn btn-info">
                                <i class="fas fa-money-bill-wave"></i> จัดการอัตราค่าตอบแทน
                            </a>

                            <a href="{{ route('layout.exports.pdf', ['id' => $student->id, 'month' => $selectedYearMonth]) }}"
                                class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> ดาวน์โหลดแบบใบเบิกค่าตอบแทน
                            </a>

                            <a href="{{ route('layout.exports.result-pdf', ['id' => $student->id, 'month' => $selectedYearMonth]) }}"
                                class="btn btn-success">
                                <i class="fas fa-file-pdf"></i> ดาวน์โหลดหลักฐานการจ่ายเงิน
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
