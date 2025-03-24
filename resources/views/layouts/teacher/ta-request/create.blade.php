@extends('layouts.teacherLayout')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>แบบฟอร์มขอผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.ta-requests.store') }}" method="POST" id="taRequestForm">
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
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $student->name }} {{ $student->student_id }}</h6>
                                        <span class="badge {{ $student->degree_level === 'bachelor' ? 'bg-primary' : 'bg-info' }}">
                                            {{ $student->degree_level === 'bachelor' ? 'ปริญญาตรี' : 'บัณฑิต' }}
                                        </span>
                                        <input type="hidden" id="student-degree_level-{{ $index }}" value="{{ $student->degree_level }}">
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
                                        <div class="mt-4 p-3 rounded total-hours-container" id="total-container-{{ $index }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">รวมชั่วโมงทำงานของผู้ช่วยสอน</h6>
                                                <div class="input-group" style="width: auto;">
                                                    <input type="text" id="total-hours-{{ $index }}"
                                                        class="form-control" readonly value="0">
                                                    <span class="input-group-text">ชั่วโมง/สัปดาห์</span>
                                                </div>
                                            </div>
                                            <div id="hours-warning-{{ $index }}" class="text-danger mt-2" style="display: none;">
                                                นักศึกษาปริญญาตรีต้องมีชั่วโมงรวมระหว่าง 10-12 ชั่วโมง/สัปดาห์
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert alert-warning" id="form-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>กรุณาแก้ไขชั่วโมงการทำงานของผู้ช่วยสอนปริญญาตรีให้อยู่ระหว่าง 10-12 ชั่วโมง/สัปดาห์</span>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="submitBtn">บันทึกคำร้อง</button>
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

                    const totalElement = document.getElementById(`total-hours-${studentIndex}`);
                    totalElement.value = total;
                    
                    // Check student degree_level and validate hours
                    const degree_level = document.getElementById(`student-degree_level-${studentIndex}`).value;
                    const warningElement = document.getElementById(`hours-warning-${studentIndex}`);
                    const totalContainer = document.getElementById(`total-container-${studentIndex}`);
                    
                    if (degree_level === 'bachelor') { // ปริญญาตรี
                        if (total < 10 || total > 12) {
                            warningElement.style.display = 'block';
                            totalContainer.classList.add('bg-danger-subtle');
                            totalContainer.classList.remove('bg-light');
                        } else {
                            warningElement.style.display = 'none';
                            totalContainer.classList.remove('bg-danger-subtle');
                            totalContainer.classList.add('bg-success-subtle');
                        }
                    } else {
                        warningElement.style.display = 'none';
                        totalContainer.classList.remove('bg-danger-subtle');
                        totalContainer.classList.add('bg-light');
                    }
                    
                    validateAllHours();
                }
                
                // Validate all hours and update form status
                function validateAllHours() {
                    let hasError = false;
                    
                    document.querySelectorAll('[id^="student-degree_level-"]').forEach(degree_levelInput => {
                        const index = degree_levelInput.id.split('-')[2];
                        const degree_level = degree_levelInput.value;
                        const totalHours = parseInt(document.getElementById(`total-hours-${index}`).value || 0);
                        
                        if (degree_level === 'bachelor' && (totalHours < 10 || totalHours > 12)) {
                            hasError = true;
                        }
                    });
                    
                    // Update submit button status and warning
                    const submitBtn = document.getElementById('submitBtn');
                    const formWarning = document.getElementById('form-warning');
                    
                    if (hasError) {
                        submitBtn.disabled = true;
                        formWarning.style.display = 'block';
                    } else {
                        submitBtn.disabled = false;
                        formWarning.style.display = 'none';
                    }
                    
                    return !hasError;
                }
                
                // Add form submission validation
                document.getElementById('taRequestForm').addEventListener('submit', function(event) {
                    if (!validateAllHours()) {
                        event.preventDefault();
                        alert('ไม่สามารถบันทึกได้: กรุณาแก้ไขชั่วโมงการทำงานของผู้ช่วยสอนปริญญาตรีให้อยู่ระหว่าง 10-12 ชั่วโมง/สัปดาห์');
                        
                        // Scroll to the form warning message
                        document.getElementById('form-warning').scrollIntoView({ 
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });
                
                // Initialize all student hour calculations
                document.querySelectorAll('[id^="student-degree_level-"]').forEach(degree_levelInput => {
                    const index = degree_levelInput.id.split('-')[2];
                    calculateTotal(index);
                });
            });
        </script>
    @endpush

    <style>
        .total-hours-container {
            transition: background-color 0.3s ease;
        }
        .bg-danger-subtle {
            background-color: #f8d7da;
        }
        .bg-success-subtle {
            background-color: #d1e7dd;
        }
    </style>
@endsection