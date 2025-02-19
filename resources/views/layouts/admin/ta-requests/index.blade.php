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
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>วันที่ยื่น</th>
                                        <th>อาจารย์</th>
                                        <th>รายวิชา</th>
                                        <th>จำนวน TA</th>
                                        <th>รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $request->teacher->name }}</td>
                                            <td>
                                                {{ $request->course->subjects->subject_id }}
                                                {{ $request->course->subjects->name_en }}
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
                                        </tr>

                                        <!-- Modal แสดงรายละเอียด -->
                                        <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">รายละเอียดคำร้อง</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                                                        <p><strong>อาจารย์:</strong> {{ $request->teacher->name }}</p>
                                                                        <p><strong>รายวิชา:</strong> {{ $request->course->subjects->subject_id }}</p>
                                                                        <p><strong>ชื่อวิชา:</strong> {{ $request->course->subjects->name_en }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>วันที่ยื่น:</strong> {{ $request->created_at->format('d/m/Y') }}</p>
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

                                                                <!-- รายละเอียด TA -->
                                                                @foreach($request->details as $detail)
                                                                    @foreach($detail->students as $student)
                                                                        <div class="card mt-3">
                                                                            <div class="card-header bg-light">
                                                                                <h6 class="mb-0">
                                                                                    {{ $student->courseTa->student->name }}
                                                                                    ({{ $student->courseTa->student->student_id }})
                                                                                </h6>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="row">
                                                                                    <div class="col-md-3">
                                                                                        <p><strong>ช่วยสอน:</strong> {{ $student->teaching_hours }} ชม./สัปดาห์</p>
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <p><strong>เตรียมสอน:</strong> {{ $student->prep_hours }} ชม./สัปดาห์</p>
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <p><strong>ตรวจงาน:</strong> {{ $student->grading_hours }} ชม./สัปดาห์</p>
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <p><strong>อื่นๆ:</strong> {{ $student->other_hours }} ชม./สัปดาห์</p>
                                                                                    </div>
                                                                                </div>
                                                                                @if($student->other_duties)
                                                                                    <p><strong>รายละเอียดงานอื่นๆ:</strong> {{ $student->other_duties }}</p>
                                                                                @endif
                                                                                <p class="mb-0"><strong>รวมชั่วโมงต่อสัปดาห์:</strong> {{ $student->total_hours_per_week }} ชั่วโมง</p>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">ไม่พบข้อมูลคำร้อง</td>
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
            .modal-lg {
                max-width: 900px;
            }
            .card-header {
                background-color: #f8f9fa;
                padding: 0.75rem 1rem;
            }
        </style>
    @endpush
@endsection