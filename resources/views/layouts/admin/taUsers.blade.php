@extends('layouts.adminLayout')
@section('title', 'จัดการข้อมูลผู้ช่วยสอน')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    {{-- ส่วนของปุ่มสำหรับเลือก semesters --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">ภาคการศึกษาสำหรับผู้ช่วยสอนและอาจารย์</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('layout.admin.updateUserSemester') }}" method="POST">
                                        @csrf
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-8">
                                                <label for="user_semester_id" class="form-label">เลือกภาคการศึกษาที่ TA
                                                    และอาจารย์จะเห็น:</label>
                                                <select name="semester_id" id="user_semester_id" class="form-select">
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester->semester_id }}"
                                                            {{ $userSelectedSemester && $userSelectedSemester->semester_id == $semester->semester_id ? 'selected' : '' }}>
                                                            {{ $semester->year }}/{{ $semester->semesters }}
                                                            ({{ \Carbon\Carbon::parse($semester->start_date)->format('d/m/Y') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($semester->end_date)->format('d/m/Y') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-primary mt-5">บันทึกการเลือก</button>
                                            </div>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            ภาคการศึกษานี้จะกำหนดข้อมูลที่ผู้ช่วยสอนและอาจารย์จะมองเห็น
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">ภาคการศึกษาสำหรับผู้ดูแลระบบ</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('layout.admin.taUsers') }}" method="GET" id="semesterForm">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-8">
                                                <label for="admin_semester_id"
                                                    class="form-label">เลือกภาคการศึกษาที่ต้องการดูข้อมูล:</label>
                                                <select name="semester_id" id="admin_semester_id" class="form-select">
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester->semester_id }}"
                                                            {{ $selectedSemester && $selectedSemester->semester_id == $semester->semester_id ? 'selected' : '' }}>
                                                            {{ $semester->year }}/{{ $semester->semesters }}
                                                            ({{ \Carbon\Carbon::parse($semester->start_date)->format('d/m/Y') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($semester->end_date)->format('d/m/Y') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-info mt-5">แสดงข้อมูล</button>
                                            </div>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            ภาคการศึกษานี้จะกำหนดข้อมูลที่แสดงในหน้านี้เท่านั้น
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4>จัดการข้อมูลผู้ช่วยสอน</h4>
                    <div class="card mb-4 p-2">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5 class="card-title">รายวิชาที่มีผู้ช่วยสอน</h5>
                                </div>
                                <div class="col-md-3">
                                    <select id="curriculumFilter" class="form-select">
                                        <option value="">ทั้งหมด (หลักสูตร)</option>
                                        @foreach ($curriculums as $curriculum)
                                            <option value="{{ $curriculum->name_th }}">{{ $curriculum->name_th }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="ค้นหารายวิชา, รหัสวิชา, หรือชื่ออาจารย์...">
                                </div>
                            </div>
                            <table class="table" id="taTable">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>รหัสวิชา</th>
                                        <th>ชื่อวิชา</th>
                                        <th>หลักสูตร</th>
                                        <th>อาจารย์ผู้สอน</th>
                                        <th>จำนวน TA ที่ได้รับอนุมัติ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coursesWithTAs as $index => $course)
                                        <tr class="table-row">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $course->subjects->subject_id ?? 'N/A' }}</td>
                                            <td>{{ $course->subjects->name_en ?? 'N/A' }}</td>
                                            <td>{{ $course->curriculums->name_th ?? 'N/A' }}</td>
                                            <td>
                                                @if ($course->teachers)
                                                    {{ $course->teachers->title_th }} {{ $course->teachers->name }}
                                                    {{ $course->teachers->lastname_th }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $course->course_tas->count() }}</td>
                                            <td>
                                                <a href="{{ route('layout.admin.detailsTa', $course->course_id) }}"
                                                    class="btn btn-primary btn-sm">
                                                    รายละเอียดผู้ช่วยสอน
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">ไม่พบข้อมูลรายวิชาที่มีผู้ช่วยสอน</td>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Script loaded'); // Debug log

                const searchInput = document.getElementById('searchInput');
                const curriculumFilter = document.getElementById('curriculumFilter');
                const tableRows = document.querySelectorAll('.table-row');

                console.log('Found rows:', tableRows.length); // Debug log

                function filterTable() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const curriculumValue = curriculumFilter.value.toLowerCase();

                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const curriculumCell = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                        const matchesSearch = text.includes(searchTerm);
                        const matchesCurriculum = curriculumValue === '' || curriculumCell.includes(
                            curriculumValue);

                        row.style.display = (matchesSearch && matchesCurriculum) ? '' : 'none';
                    });
                }

                if (searchInput && tableRows.length > 0) {
                    searchInput.addEventListener('input', filterTable);
                }

                if (curriculumFilter && tableRows.length > 0) {
                    curriculumFilter.addEventListener('change', filterTable);
                }
            });
        </script>
    @endpush
@endsection
