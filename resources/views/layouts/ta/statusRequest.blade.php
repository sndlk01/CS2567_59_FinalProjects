@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'สถานะคำร้องการสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container mt-4">
                        <h4 class="mb-3">คำร้องการสมัครผู้ช่วยสอน</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        {{-- <th>รหัสนักศึกษา</th> --}}
                                        {{-- <th>ชื่อ-นามสกุล</th> --}}
                                        <th>รายวิชาที่สมัคร</th>
                                        <th>วันที่สมัคร</th>
                                        <th>สถานะการสมัคร</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>ความคิดเห็น</th>
                                        <th>แก้ไข/ลบ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requests as $index => $request)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            {{-- <td>{{ $request['student_id'] }}</td> --}}
                                            {{-- <td>{{ $request['full_name'] }}</td> --}}
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
                                                    {{ \Carbon\Carbon::parse($request['approved_at'])->format('d-m-Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $request['comment'] ?? 'ไม่มีความคิดเห็น' }}</td>
                                            <td>
                                                @if ($status === 'w')
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm edit-request-btn me-2"
                                                        data-student-id="{{ $request['student_id'] }}"
                                                        data-course="{{ $request['course'] }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('requests.destroy', $request['student_id']) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบคำร้องนี้?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">ไม่พบข้อมูลคำร้องการสมัคร</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">แก้ไขคำร้องการสมัคร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject" class="form-label">เลือกรายวิชา</label>
                            <select class="form-select" id="subject" name="subject_id" required>
                                <option value="">เลือกรายวิชา</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เลือกกลุ่มเรียน</label>
                            <div id="sectionsContainer">
                                <!-- Sections will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Store the subjects data
                const subjectsWithSections = @json($subjectsWithSections);

                // Add click event listeners to all edit buttons
                document.querySelectorAll('.edit-request-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const studentId = this.dataset.studentId;
                        const currentCourse = this.dataset.course;

                        console.log('Opening modal for student:', studentId); // Debug line

                        // Get modal element
                        const modalElement = document.getElementById('editModal');
                        if (!modalElement) {
                            console.error('Modal element not found');
                            return;
                        }

                        // Initialize Bootstrap modal
                        const modal = new bootstrap.Modal(modalElement);

                        // Set form action
                        const form = document.getElementById('editForm');
                        if (form) {
                            form.action = `/requests/${studentId}`;
                        }

                        // Populate subjects dropdown
                        const subjectSelect = document.getElementById('subject');
                        if (subjectSelect) {
                            subjectSelect.innerHTML = '<option value="">เลือกรายวิชา</option>';

                            subjectsWithSections.forEach(item => {
                                const subject = item.subject;
                                const option = new Option(
                                    `${subject.subject_id} ${subject.subject_name_en}`,
                                    subject.subject_id
                                );
                                // Pre-select the current course if it matches
                                if (currentCourse.includes(subject.subject_id)) {
                                    option.selected = true;
                                }
                                subjectSelect.add(option);
                            });

                            // Trigger change event to populate sections if a subject is selected
                            if (subjectSelect.value) {
                                subjectSelect.dispatchEvent(new Event('change'));
                            }
                        }

                        // Show modal
                        modal.show();
                    });
                });

                // Add subject change event listener
                const subjectSelect = document.getElementById('subject');
                if (subjectSelect) {
                    subjectSelect.addEventListener('change', function() {
                        const subjectId = this.value;
                        const sectionsContainer = document.getElementById('sectionsContainer');
                        if (!sectionsContainer) return;

                        sectionsContainer.innerHTML = '';

                        if (subjectId) {
                            const subject = subjectsWithSections.find(item =>
                                item.subject.subject_id === subjectId
                            );

                            if (subject && subject.sections) {
                                subject.sections.forEach(section => {
                                    const div = document.createElement('div');
                                    div.className = 'form-check mb-2';
                                    div.innerHTML = `
                                <input class="form-check-input" type="checkbox" 
                                       name="sections[]" value="${section}" 
                                       id="section${section}">
                                <label class="form-check-label" for="section${section}">
                                    กลุ่มที่ ${section}
                                </label>
                            `;
                                    sectionsContainer.appendChild(div);
                                });
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
