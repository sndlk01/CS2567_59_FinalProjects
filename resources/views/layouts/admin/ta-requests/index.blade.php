@extends('layouts.adminLayout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">รายการคำร้องขอผู้ช่วยสอน</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>วันที่ยื่น</th>
                                        <th>อาจารย์</th>
                                        <th>รายวิชา</th>
                                        <th>จำนวน TA</th>
                                        <th>รายละเอียด</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $request->teacher->name }}</td>
                                            <td>
                                                {{ $request->course->subjects->subject_id }} {{ $request->course->subjects->name_en }}
                                            </td>
                                           
                                            <td>
                                                {{ $request->details->sum(function ($detail) {
                                                    return $detail->students->count();
                                                }) }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal{{ $request->id }}">
                                                    รายละเอียด
                                                </button>
                                            </td>
                                            <td>
                                                @if ($request->status === 'P')
                                                    <form action="{{ route('admin.ta-requests.process', $request->id) }}"
                                                        method="POST" class="d-flex align-items-center gap-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="form-select form-select-sm"
                                                            style="width: auto;">
                                                            <option value="P" selected>รอดำเนินการ</option>
                                                            <option value="A">อนุมัติ</option>
                                                            <option value="R">ไม่อนุมัติ</option>
                                                        </select>
                                                        <button type="submit"
                                                            class="btn btn-primary btn-sm">บันทึก</button>
                                                    </form>
                                                @else
                                                    @switch($request->status)
                                                        @case('A')
                                                            <span class="badge bg-success">อนุมัติแล้ว</span>
                                                        @break

                                                        @case('R')
                                                            <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                        @break
                                                    @endswitch
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Modal แสดงรายละเอียด -->
                                        <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">รายละเอียดคำร้อง</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- ข้อมูลพื้นฐาน -->
                                                        <div class="card mb-3">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">ข้อมูลรายวิชา</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <p><strong>อาจารย์:</strong>
                                                                            {{ $request->teacher->name }}</p>
                                                                        <p><strong>รายวิชา:</strong>
                                                                            {{ $request->course->subjects->subject_id }}
                                                                        </p>
                                                                        <p><strong>ชื่อวิชา:</strong>
                                                                            {{ $request->course->subjects->name_en }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>วันที่ยื่น:</strong>
                                                                            {{ $request->created_at->format('d/m/Y') }}</p>
                                                                        <p><strong>ประเภท:</strong>
                                                                            @switch($request->payment_type)
                                                                                @case('lecture')
                                                                                    บรรยาย
                                                                                @break

                                                                                @case('lab')
                                                                                    ปฏิบัติการ
                                                                                @break

                                                                                @case('both')
                                                                                    ทั้งบรรยายและปฏิบัติการ
                                                                                @break
                                                                            @endswitch
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    @foreach ($request->details as $detail)
                                                                        @foreach ($detail->students as $student)
                                                                            <div class="col-12 mb-3">
                                                                                <div class="card h-100">
                                                                                    <div class="card-body">
                                                                                        <div class="row align-items-center">
                                                                                            <!-- ข้อมูลนักศึกษา -->
                                                                                            <div class="col-md-4">
                                                                                                <h6 class="mb-1">{{ $student->courseTa->student->name }}</h6>
                                                                                                <div class="text-muted">รหัสนักศึกษา: {{ $student->courseTa->student->student_id }}</div>
                                                                                            </div>
                                                                                            
                                                                                            <!-- ชั่วโมงการทำงาน -->
                                                                                            <div class="col-md-8">
                                                                                                <div class="row g-3">
                                                                                                    <div class="col-md-3">
                                                                                                        <div class="text-center p-2 rounded bg-light">
                                                                                                            <div class="small text-muted">ช.ม.สอน</div>
                                                                                                            <div class="fw-bold">{{ $student->teaching_hours }}</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-3">
                                                                                                        <div class="text-center p-2 rounded bg-light">
                                                                                                            <div class="small text-muted">ช.ม.เตรียม</div>
                                                                                                            <div class="fw-bold">{{ $student->prep_hours }}</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-3">
                                                                                                        <div class="text-center p-2 rounded bg-light">
                                                                                                            <div class="small text-muted">ช.ม.ตรวจงาน</div>
                                                                                                            <div class="fw-bold">{{ $student->grading_hours }}</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-3">
                                                                                                        <div class="text-center p-2 rounded bg-light">
                                                                                                            <div class="small text-muted">รวม/สัปดาห์</div>
                                                                                                            <div class="fw-bold">{{ $student->total_hours_per_week }}</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                
                                                                                                @if($student->other_hours > 0)
                                                                                                    <div class="mt-2">
                                                                                                        <div class="text-center p-2 rounded bg-light">
                                                                                                            <div class="small text-muted">ชั่วโมงอื่นๆ</div>
                                                                                                            <div class="fw-bold">{{ $student->other_hours }}</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                
                                                                                        @if ($student->other_duties)
                                                                                            <div class="mt-3 pt-3 border-top">
                                                                                                <div class="text-muted">
                                                                                                    <strong>งานอื่นๆ:</strong> {{ $student->other_duties }}
                                                                                                </div>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endforeach
                                                                
                                                                    <!-- สรุปจำนวน TA -->
                                                                    <div class="col-12">
                                                                        <div class="card bg-light">
                                                                            <div class="card-body text-end">
                                                                                <strong>จำนวน TA ทั้งหมด:</strong>
                                                                                <span class="ms-2">
                                                                                    {{ $request->details->sum(function($detail) {
                                                                                        return $detail->students->count();
                                                                                    }) }} คน
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        @if ($request->status !== 'P')
                                                            <div class="mt-4">
                                                                <p><strong>สถานะ:</strong>
                                                                    @switch($request->status)
                                                                        @case('A')
                                                                            <span class="badge bg-success">อนุมัติแล้ว</span>
                                                                        @break

                                                                        @case('R')
                                                                            <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                                        @break
                                                                    @endswitch
                                                                </p>
                                                                @if ($request->admin_comment)
                                                                    <p><strong>หมายเหตุ:</strong>
                                                                        {{ $request->admin_comment }}</p>
                                                                @endif
                                                                @if ($request->admin_processed_at)
                                                                    <p><strong>ดำเนินการเมื่อ:</strong>
                                                                        {{ $request->admin_processed_at->format('d/m/Y H:i') }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">ไม่พบข้อมูลคำร้อง</td>
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

        @push('styles')
            <style>
                .table td {
                    vertical-align: middle;
                }

                .badge {
                    font-size: 0.875em;
                }

                .gap-2 {
                    gap: 0.5rem !important;
                }

                .d-flex {
                    display: flex !important;
                }

                .align-items-center {
                    align-items: center !important;
                }

                .form-select-sm {
                    min-width: 120px;
                }

                .modal-lg {
                    max-width: 900px;
                }

                .card-header {
                    background-color: #f8f9fa;
                    padding: 0.75rem 1rem;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // จัดการการเปลี่ยนสถานะ
                    const statusSelects = document.querySelectorAll('select[name="status"]');

                    statusSelects.forEach(select => {
                        const form = select.closest('form');

                        select.addEventListener('change', function() {
                            if (this.value === 'R') {
                                const reason = prompt('กรุณาระบุเหตุผลที่ไม่อนุมัติ:');
                                if (!reason || reason.trim() === '') {
                                    this.value = 'P';
                                    return;
                                }

                                // เพิ่ม input field สำหรับ comment
                                let commentInput = form.querySelector('input[name="comment"]');
                                if (!commentInput) {
                                    commentInput = document.createElement('input');
                                    commentInput.type = 'hidden';
                                    commentInput.name = 'comment';
                                    form.appendChild(commentInput);
                                }
                                commentInput.value = reason;
                            }
                        });

                        // ตรวจสอบก่อน submit
                        form.addEventListener('submit', function(e) {
                            const status = select.value;
                            if (status === 'P') {
                                e.preventDefault();
                                alert('กรุณาเลือกสถานะ');
                                return;
                            }

                            if (!confirm('ยืนยันการเปลี่ยนสถานะ?')) {
                                e.preventDefault();
                            }
                        });
                    });
                });
            </script>
        @endpush

    @endsection
