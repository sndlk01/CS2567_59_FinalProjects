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

                    <!-- ประวัติการเป็น TA -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ประวัติการเป็นผู้ช่วยสอน</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ลำดับ</th>
                                            <th>รหัสวิชา</th>
                                            <th>ชื่อวิชา</th>
                                            <th>ปีการศึกษา</th>
                                            <th>อาจารย์ผู้สอน</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($student->courseTas as $index => $courseTa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $courseTa->course->subjects->subject_id }}</td>
                                                <td>{{ $courseTa->course->subjects->name_en }}</td>
                                                <td>
                                                    {{ $courseTa->course->semesters->semesters }}/{{ $courseTa->course->semesters->year }}
                                                </td>
                                                <td>
                                                    {{ $courseTa->course->teachers->position }}
                                                    {{ $courseTa->course->teachers->name }}
                                                </td>
                                                <td>
                                                    @php
                                                        $request = $courseTa->courseTaClasses->flatMap->requests
                                                            ->sortByDesc('created_at')
                                                            ->first();
                                                    @endphp
                                                    @if ($request && $request->status === 'A')
                                                        <span class="badge bg-success">อนุมัติ</span>
                                                    @else
                                                        <span class="badge bg-secondary">ไม่ระบุ</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">ไม่พบประวัติการเป็นผู้ช่วยสอน</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
