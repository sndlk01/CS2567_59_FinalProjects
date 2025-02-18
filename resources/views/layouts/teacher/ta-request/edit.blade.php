@extends('layouts.teacherLayout')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>แก้ไขคำร้องขอผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.ta-requests.update', $request->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- ข้อมูลรายวิชา -->
                    <div class="mb-4">
                        <h5>1. รหัส-ชื่อวิชา</h5>
                        <p>{{ $request->course->subjects->subject_id }} {{ $request->course->subjects->name_en }}</p>
                    </div>

                    <div class="mb-4">
                        <h5>2. มีความประสงค์เบิกค่าตอบแทน</h5>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="lecture" class="form-check-input" required
                                {{ $request->payment_type === 'lecture' ? 'checked' : '' }}>
                            <label class="form-check-label">เบิกเฉพาะบรรยาย (Lec.)</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="lab" class="form-check-input"
                                {{ $request->payment_type === 'lab' ? 'checked' : '' }}>
                            <label class="form-check-label">เบิกเฉพาะปฏิบัติการ (Lab.)</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="both" class="form-check-input"
                                {{ $request->payment_type === 'both' ? 'checked' : '' }}>
                            <label class="form-check-label">เบิกทั้งบรรยายและปฏิบัติการ (Lec.+Lab)</label>
                        </div>
                    </div>

                    <!-- รายชื่อ TA -->
                    <div class="mb-4">
                        <h5 class="mb-3">3. รายชื่อและภาระงานผู้ช่วยสอน</h5>
                        <div id="ta-list">
                            @foreach($request->details as $detail)
                                @foreach($detail->students as $index => $student)
                                    <div class="ta-item card shadow-sm mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ $student->courseTa->student->name }}
                                                ({{ $student->courseTa->student->student_id }})</h6>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" name="students[{{ $index }}][course_ta_id]"
                                                value="{{ $student->course_ta_id }}">

                                            <div class="row gy-3">
                                                <!-- ช่วยสอน -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">ช่วยสอน</label>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            name="students[{{ $index }}][teaching_hours]"
                                                            class="form-control calculate-total" required min="0"
                                                            data-student="{{ $index }}"
                                                            value="{{ $student->teaching_hours }}">
                                                        <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                    </div>
                                                </div>

                                                <!-- ช่วยเตรียมการสอน -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">ช่วยเตรียมการสอน</label>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            name="students[{{ $index }}][prep_hours]"
                                                            class="form-control calculate-total" required min="0"
                                                            data-student="{{ $index }}"
                                                            value="{{ $student->prep_hours }}">
                                                        <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                    </div>
                                                </div>

                                                <!-- ตรวจแบบทดสอบ -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">ตรวจแบบทดสอบ</label>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            name="students[{{ $index }}][grading_hours]"
                                                            class="form-control calculate-total" required min="0"
                                                            data-student="{{ $index }}"
                                                            value="{{ $student->grading_hours }}">
                                                        <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                    </div>
                                                </div>

                                                <!-- อื่นๆ -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">อื่นๆ</label>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            name="students[{{ $index }}][other_hours]"
                                                            class="form-control calculate-total" min="0"
                                                            data-student="{{ $index }}"
                                                            value="{{ $student->other_hours }}">
                                                        <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                    </div>
                                                </div>

                                                @if($student->other_duties)
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">รายละเอียดงานอื่นๆ</label>
                                                        <textarea name="students[{{ $index }}][other_duties]"
                                                            class="form-control">{{ $student->other_duties }}</textarea>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- รวมชั่วโมง -->
                                            <div class="mt-4 p-3 bg-light rounded">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">รวมชั่วโมงทำงานของผู้ช่วยสอน</h6>
                                                    <div class="input-group" style="width: auto;">
                                                        <input type="text" id="total-hours-{{ $index }}"
                                                            class="form-control" readonly
                                                            value="{{ $student->total_hours_per_week }}">
                                                        <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                        <a href="{{ route('teacher.ta-requests.show', $request->id) }}" class="btn btn-secondary ms-2">
                            ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // คำนวณรวมชั่วโมงเมื่อมีการเปลี่ยนแปลงค่าใดๆ
                document.querySelectorAll('.calculate-total').forEach(input => {
                    input.addEventListener('input', function() {
                        const studentIndex = this.dataset.student;
                        calculateTotal(studentIndex);
                    });
                });

                function calculateTotal(studentIndex) {
                    const inputs = document.querySelectorAll(`[data-student="${studentIndex}"]`);
                    let total = 0;

                    inputs.forEach(input => {
                        total += parseInt(input.value || 0);
                    });

                    document.getElementById(`total-hours-${studentIndex}`).value = total;
                }
            });
        </script>
    @endpush
@endsection