@extends('layouts.teacherLayout')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">รายละเอียดคำร้องขอผู้ช่วยสอน</h4>
            </div>
            <div class="card-body">
                <!-- ข้อมูลรายวิชา -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">1. ข้อมูลรายวิชา</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="fw-bold" style="width: 120px;">รหัสวิชา:</div>
                                <div>{{ $course->subjects->subject_id }}</div>
                            </div>
                            <div class="d-flex">
                                <div class="fw-bold" style="width: 120px;">ชื่อวิชา:</div>
                                <div>{{ $course->subjects->name_en }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="fw-bold" style="width: 120px;">อาจารย์ผู้สอน:</div>
                                <div>{{ $teacher->name }}</div>
                            </div>
                            <div class="d-flex">
                                <div class="fw-bold" style="width: 120px;">วันที่ยื่นคำร้อง:</div>
                                <div>{{ $request->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ประเภทการเบิกจ่าย -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">2. ประเภทการเบิกจ่าย</h5>
                    <div class="d-flex align-items-center">
                        <div class="fw-bold" style="width: 120px;">ประเภท:</div>
                        <div>
                            @switch($request->payment_type)
                                @case('lecture')
                                    เบิกเฉพาะบรรยาย (Lec.)
                                @break

                                @case('lab')
                                    เบิกเฉพาะปฏิบัติการ (Lab.)
                                @break

                                @case('both')
                                    เบิกทั้งบรรยายและปฏิบัติการ (Lec.+Lab)
                                @break
                            @endswitch
                        </div>
                    </div>
                </div>

                <!-- รายละเอียด TA -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">3. รายละเอียดผู้ช่วยสอน</h5>
                    @foreach ($details as $detail)
                        @foreach ($detail->students as $student)
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ $student->courseTa->student->name }}
                                        ({{ $student->courseTa->student->student_id }})
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td style="width: 200px;">ช่วยสอน:</td>
                                            <td>{{ $student->teaching_hours }} ชั่วโมง/สัปดาห์</td>
                                        </tr>
                                        <tr>
                                            <td>เตรียมการสอน:</td>
                                            <td>{{ $student->prep_hours }} ชั่วโมง/สัปดาห์</td>
                                        </tr>
                                        <tr>
                                            <td>ตรวจงาน:</td>
                                            <td>{{ $student->grading_hours }} ชั่วโมง/สัปดาห์</td>
                                        </tr>
                                        @if ($student->other_hours)
                                            <tr>
                                                <td>อื่นๆ:</td>
                                                <td>{{ $student->other_hours }} ชั่วโมง/สัปดาห์</td>
                                            </tr>
                                        @endif
                                        <tr class="table-secondary fw-bold">
                                            <td>รวม:</td>
                                            <td>{{ $student->total_hours_per_week }} ชั่วโมง/สัปดาห์</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                <!-- สถานะคำร้อง -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">4. สถานะคำร้อง</h5>
                    <div class="d-flex align-items-center">
                        <div class="fw-bold" style="width: 120px;">สถานะ:</div>
                        <div>
                            @switch($request->status)
                                @case('W')
                                    <span class="badge bg-warning">รอดำเนินการ</span>
                                @break

                                @case('A')
                                    <span class="badge bg-success">อนุมัติ</span>
                                @break

                                @case('R')
                                    <span class="badge bg-danger">ไม่อนุมัติ</span>
                                @break
                            @endswitch
                        </div>
                    </div>
                </div>

                <div class="text-center">

                   
                    <a href="{{ route('teacher.ta-requests.index') }}" class="btn btn-secondary px-4">
                        กลับไปหน้ารายการ
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
