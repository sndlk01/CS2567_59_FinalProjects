@extends('layouts.teacherLayout')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>แบบฟอร์มขอผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.ta-requests.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->course_id }}">

                    <!-- ข้อมูลรายวิชา -->
                    <div class="mb-4">
                        <h5>1. รหัส-ชื่อวิชา</h5>
                        <p>{{ $course->subjects->subject_id }} {{ $course->subjects->name_en }}</p>
                    </div>

                    <div class="mb-4">
                        <h5>2. มีความประสงค์เบิกค่าตอบแทน</h5>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="lecture" class="form-check-input" required>
                            <label class="form-check-label">เบิกเฉพาะบรรยาย (Lec.)</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="lab" class="form-check-input">
                            <label class="form-check-label">เบิกเฉพาะปฏิบัติการ (Lab.)</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="payment_type" value="both" class="form-check-input">
                            <label class="form-check-label">เบิกทั้งบรรยายและปฏิบัติการ (Lec.+Lab)</label>
                        </div>
                    </div>

                    <!-- รายชื่อ TA -->
                    <div class="mb-4">
                        <h5 class="mb-3">3. รายชื่อและภาระงานผู้ช่วยสอน</h5>
                        <div id="ta-list">
                            @foreach ($availableStudents as $index => $student)
                                <div class="ta-item card shadow-sm mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ $student->name }} {{ $student->student_id }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="students[{{ $index }}][course_ta_id]"
                                            value="{{ $student->courseTas->first()?->id }}">

                                        <div class="row gy-3">
                                            <!-- ช่วยสอน -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">ช่วยสอน</label>
                                                <div class="input-group">
                                                    <input type="number"
                                                        name="students[{{ $index }}][teaching_hours]"
                                                        class="form-control calculate-total" required min="0"
                                                        data-student="{{ $index }}">
                                                    <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                </div>
                                            </div>

                                            <!-- ช่วยเตรียมการสอน -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">ช่วยเตรียมการสอน</label>
                                                <div class="input-group">
                                                    <input type="number" name="students[{{ $index }}][prep_hours]"
                                                        class="form-control calculate-total" required min="0"
                                                        data-student="{{ $index }}">
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
                                                        data-student="{{ $index }}">
                                                    <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                </div>
                                            </div>

                                            <!-- อื่นๆ -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">อื่นๆ</label>
                                                <div class="input-group">
                                                    <input type="number" name="students[{{ $index }}][other_hours]"
                                                        class="form-control calculate-total" min="0"
                                                        data-student="{{ $index }}">
                                                    <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- รวมชั่วโมง -->
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">รวมชั่วโมงทำงานของผู้ช่วยสอน</h6>
                                                <div class="input-group" style="width: auto;">
                                                    <input type="text" id="total-hours-{{ $index }}"
                                                        class="form-control" readonly value="0">
                                                    <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">บันทึกคำร้อง</button>
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
