@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'ยื่นคำร้องสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container">
                        <h4 class="mb-3">ยื่นคำร้องสมัครผู้ช่วยสอน</h4>
                        <form method="POST" action="{{ route('ta.apply') }}" class="border-top pt-2">
                            @csrf
                            <h5 class="mb-3">แบบฟอร์มกรอกรายละเอียดผู้ช่วยสอน</h5>
                            <div class="row mb-3">
                                <div class="col-md-1">
                                    <input type="text" class="form-control" placeholder="คำนำหน้า"
                                        value="{{ Auth::user()->prefix ?? 'N/A : คำนำหน้า' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="ชื่อ"
                                        value="{{ Auth::user()->name ?? 'N/A : ชื่อ' }}" disabled>
                                </div>
                                <div class="col-md-3">
                                    <input type="tel" class="form-control" placeholder="รหัสนักศึกษา"
                                        value="{{ Auth::user()->student_id ?? 'N/A : นักศึกษา' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="อีเมล"
                                        value="{{ Auth::user()->email ?? 'N/A : อีเมล' }}" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">

                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="รหัสบัตรประจำตัวประชาชน"
                                        value="{{ Auth::user()->card_id ?? 'N/A : รหัสบัตรประจำตัวประชาชน' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="เบอร์โทรศัพท์"
                                        value="{{ Auth::user()->phone ?? 'N/A : เบอร์โทรศัพท์' }}" disabled>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <strong>ภาคการศึกษาปัจจุบัน:</strong> 
                                {{ $currentSemester['semester'] }}/{{ $currentSemester['year'] }}
                            </div>
                            <div class="col-md-6">
                                <strong>ระยะเวลาของภาคการศึกษา:</strong> 
                                {{ \Carbon\Carbon::parse($currentSemester['start_date'])->format('d/m/Y') }} - 
                                {{ \Carbon\Carbon::parse($currentSemester['end_date'])->format('d/m/Y') }}
                            </div>

                            <!-- พื้นที่แสดงรายวิชาที่เลือก -->
                            <div class="mb-3">
                                <label>วิชาที่คุณเลือก:</label>
                                <p id="selectedSubjects" class="border rounded p-2">ยังไม่ได้เลือกวิชา</p>
                            </div>

                            {{-- ส่วนของการเลือกวิชา --}}
                            <div class="mb-3">
                                <label class="form-label">เลือกรายวิชาและเซคชันที่ต้องการสมัคร</label>
                                <input type="text" id="subjectSearch" class="form-control mb-3"
                                        placeholder="ค้นหารายวิชา...">
                                <div class="subject-container"
                                    style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                                    {{-- ส่วนสำหรับการค้นหารายวิชา --}}
                                    
                                    @foreach ($subjectsWithSections as $index => $item)
                                        <div class="subject-item mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input subject-checkbox" type="checkbox"
                                                    name="applications[{{ $index }}][subject_id]"
                                                    value="{{ $item['subject']['subject_id'] }}"
                                                    id="subject{{ $item['subject']['subject_id'] }}">
                                                <label class="form-check-label"
                                                    for="subject{{ $item['subject']['subject_id'] }}">
                                                    {{ $item['subject']['subject_id'] }}
                                                    {{ $item['subject']['subject_name_th'] }}
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $item['subject']['subject_name_en'] }}</small>
                                                </label>
                                            </div>
                                            <div class="sections-container ml-4 mt-2" style="display: none;">
                                                @foreach ($item['sections'] as $section)
                                                    <div class="form-check">
                                                        <input class="form-check-input section-checkbox" type="checkbox"
                                                            name="applications[{{ $index }}][sections][]"
                                                            value="{{ $section }}"
                                                            id="section-{{ $item['subject']['subject_id'] }}-{{ $section }}">
                                                        <label class="form-check-label"
                                                            for="section-{{ $item['subject']['subject_id'] }}-{{ $section }}">
                                                            Section {{ $section }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-danger">*** นักศึกษาสามารถเป็นผู้ช่วยสอนได้ไม่เกิน 3 รายวิชา</small>
                            </div>

                            <button type="submit" class="btn btn-success">ยืนยันการสมัคร</button>

                            {{-- Flash Message --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                                            class="bi bi-x"></i></button>
                                </div>
                            @elseif (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                                            class="bi bi-x"></i></button>
                                </div>
                            @endif
                        </form>
                        {{-- javaScript สำหรับการเลือกรายวิชาผู้ช่วยสอน --}}
                        <script>
                            // ตรวจจับการเปลี่ยนแปลงการเลือก checkbox
                            const checkboxes = document.querySelectorAll('.subject-checkbox');
                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    updateSelectedSubjects();
                                });
                            });

                            function updateSelectedSubjects() {
                                const selectedCheckboxes = Array.from(document.querySelectorAll('.subject-checkbox:checked'));
                                // const selectedSubjects = selectedCheckboxes.map(checkbox => checkbox.nextElementSibling.textContent.trim()).join(', ');
                                const selectedSubjects = selectedCheckboxes.map(checkbox => checkbox.value).join(', ');

                                if (selectedCheckboxes.length > 0) {
                                    document.getElementById('selectedSubjects').textContent = selectedSubjects;
                                } else {
                                    document.getElementById('selectedSubjects').textContent = 'ยังไม่ได้เลือกวิชา';
                                }

                                // ตรวจสอบถ้าเลือกเกิน 3 วิชา
                                if (selectedCheckboxes.length > 3) {
                                    alert('คุณสามารถเลือกวิชาได้ไม่เกิน 3 วิชา');
                                    selectedCheckboxes.forEach(checkbox => {
                                        checkbox.checked = false; // ยกเลิกการเลือก
                                    });
                                    document.getElementById('selectedSubjects').textContent = 'ยังไม่ได้เลือกวิชา';
                                }
                            }
                            // ฟังก์ชันสำหรับการค้นหารายวิชา
                            document.getElementById('subjectSearch').addEventListener('input', function() {
                                const searchText = this.value.toLowerCase();
                                const subjectItems = document.querySelectorAll('.subject-item');
                                subjectItems.forEach(item => {
                                    const subjectText = item.textContent.toLowerCase();
                                    if (subjectText.includes(searchText)) {
                                        item.style.display = ''; // แสดงรายการที่ค้นพบ
                                    } else {
                                        item.style.display = 'none'; // ซ่อนรายการที่ไม่ตรงกับการค้นหา
                                    }
                                });
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const subjectCheckboxes = document.querySelectorAll('.subject-checkbox');
                                const maxSubjects = 3;

                                subjectCheckboxes.forEach(checkbox => {
                                    checkbox.addEventListener('change', function() {
                                        const sectionsContainer = this.closest('.subject-item').querySelector(
                                            '.sections-container');
                                        sectionsContainer.style.display = this.checked ? 'block' : 'none';

                                        const checkedSubjects = document.querySelectorAll('.subject-checkbox:checked');
                                        if (checkedSubjects.length > maxSubjects) {
                                            this.checked = false;
                                            sectionsContainer.style.display = 'none';
                                            alert('คุณสามารถเลือกได้ไม่เกิน 3 วิชา');
                                        }
                                    });
                                });
                            });
                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
