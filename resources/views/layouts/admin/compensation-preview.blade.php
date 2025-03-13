@extends('layouts.adminLayout')

@section('title', 'ตัวอย่างการคำนวณค่าตอบแทน')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">ตัวอย่างการคำนวณค่าตอบแทน</h4>
                        <a href="{{ route('admin.ta.profile', $compensationData['student']->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> กลับไปหน้าข้อมูลผู้ช่วยสอน
                        </a>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>ข้อมูลผู้ช่วยสอน</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">รหัสนักศึกษา</th>
                                            <td>{{ $compensationData['student']->student_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>ชื่อ-สกุล</th>
                                            <td>{{ $compensationData['student']->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>ระดับการศึกษา</th>
                                            <td>{{ $compensationData['student']->degree_level == 'graduate' ? 'บัณฑิตศึกษา' : 'ปริญญาตรี' }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>ข้อมูลรายวิชา</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">รหัสวิชา</th>
                                            <td>{{ $compensationData['course']->subjects->subject_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>ชื่อวิชา</th>
                                            <td>{{ $compensationData['course']->subjects->name_en }}</td>
                                        </tr>
                                        <tr>
                                            <th>ประจำเดือน</th>
                                            <td>{{ $compensationData['month_name'] }} {{ $compensationData['year'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <h5>จำนวนชั่วโมงการสอน</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-center">ภาคปกติ</th>
                                        <th colspan="2" class="text-center">ภาคพิเศษ</th>
                                        <th rowspan="2" class="text-center align-middle">รวมทั้งหมด</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">
                                            {{ number_format($compensationData['hours']['regularLecture'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['hours']['regularLab'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['hours']['specialLecture'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['hours']['specialLab'], 2) }}</td>
                                        <td class="text-center">{{ number_format($compensationData['hours']['total'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5>อัตราค่าตอบแทน (บาทต่อชั่วโมง)</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-center">ภาคปกติ</th>
                                        <th colspan="2" class="text-center">ภาคพิเศษ</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">
                                            {{ number_format($compensationData['rates']['regularLecture'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['rates']['regularLab'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['rates']['specialLecture'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['rates']['specialLab'], 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- เพิ่มส่วนแสดงผลการเหมาจ่ายสำหรับบัณฑิตในภาคพิเศษ -->
                        @if (isset($compensationData['is_fixed_payment']) && $compensationData['is_fixed_payment'])
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> การเหมาจ่ายสำหรับผู้ช่วยสอนระดับบัณฑิตศึกษาในภาคพิเศษ
                                </h5>
                                <p>ผู้ช่วยสอนระดับบัณฑิตศึกษาในภาคพิเศษจะได้รับค่าตอบแทนแบบเหมาจ่ายในอัตรา
                                    {{ number_format($compensationData['fixed_amount'], 2) }} บาทต่อเดือน</p>
                            </div>
                        @endif

                        <h5>การคำนวณค่าตอบแทน</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-center">ภาคปกติ</th>
                                        <th colspan="2" class="text-center">ภาคพิเศษ</th>
                                        <th rowspan="2" class="text-center align-middle">รวมทั้งหมด</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                        <th class="text-center">บรรยาย</th>
                                        <th class="text-center">ปฏิบัติการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">
                                            {{ number_format($compensationData['pay']['regularLecture'], 2) }}</td>
                                        <td class="text-center">
                                            {{ number_format($compensationData['pay']['regularLab'], 2) }}</td>

                                        @if (isset($compensationData['is_fixed_payment']) && $compensationData['is_fixed_payment'])
                                            <td class="text-center" colspan="2">
                                                {{ number_format($compensationData['fixed_amount'], 2) }} <br>
                                                <small class="text-muted">(เหมาจ่าย)</small>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                {{ number_format($compensationData['pay']['specialLecture'], 2) }}</td>
                                            <td class="text-center">
                                                {{ number_format($compensationData['pay']['specialLab'], 2) }}</td>
                                        @endif

                                        <td class="text-center">{{ number_format($compensationData['pay']['total'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5>ข้อมูลงบประมาณ</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%">จำนวนนักศึกษาในรายวิชา</th>
                                        <td>{{ number_format($compensationData['student_count'] ?? 0) }} คน</td>
                                    </tr>
                                    <tr>
                                        <th>งบประมาณรวมของรายวิชา</th>
                                        <td>{{ number_format($compensationData['total_budget'] ?? 0) }} บาท</td>
                                    </tr>
                                    <tr>
                                        <th>งบประมาณที่ใช้ไปแล้ว</th>
                                        <td>{{ number_format($compensationData['total_budget'] - $compensationData['remainingBudgetForTA'], 2) }}
                                            บาท</td>
                                    </tr>
                                    <tr>
                                        <th>งบประมาณคงเหลือรายวิชา</th>
                                        <td>{{ number_format($compensationData['remainingBudgetForTA'] ?? 0) }} บาท</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ส่วนแสดงงบประมาณไม่เพียงพอ -->
                        @if (isset($compensationData['is_exceeded']) && $compensationData['is_exceeded'])
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> งบประมาณคงเหลือไม่เพียงพอ</h5>
                                <p>งบประมาณคงเหลือของรายวิชา
                                    ({{ number_format($compensationData['remainingBudgetForTA'], 2) }} บาท)
                                    น้อยกว่าค่าตอบแทนที่คำนวณได้ ({{ number_format($compensationData['pay']['total'], 2) }}
                                    บาท)</p>
                            </div>
                        @endif

                        @php
                            // ตรวจสอบว่ามีการเบิกจ่ายแล้วหรือไม่
                            $transaction = \App\Models\CompensationTransaction::where(
                                'student_id',
                                $compensationData['student']->id,
                            )
                                ->where('course_id', $compensationData['course']->course_id)
                                ->where('month_year', $compensationData['year_month'])
                                ->first();
                            $isCompensated = $transaction ? true : false;
                        @endphp

                        @if (!$isCompensated)
                            <form action="{{ route('admin.course-budgets.compensation.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $compensationData['student']->id }}">
                                <input type="hidden" name="course_id"
                                    value="{{ $compensationData['course']->course_id }}">
                                <input type="hidden" name="month_year" value="{{ $compensationData['year_month'] }}">
                                <input type="hidden" name="hours_worked" value="{{ $compensationData['total_hours'] }}">
                                <input type="hidden" name="calculated_amount"
                                    value="{{ $compensationData['pay']['total'] }}">

                                <div class="form-group mb-3">
                                    <label for="actual_amount">จำนวนเงินที่จะเบิกจ่ายจริง:</label>
                                    <input type="number" id="actual_amount" name="actual_amount" class="form-control"
                                        value="{{ isset($compensationData['final_amount']) ? number_format($compensationData['final_amount'], 2, '.', '') : number_format($compensationData['pay']['total'], 2, '.', '') }}"
                                        max="{{ $compensationData['remainingBudgetForTA'] ?? 0 }}" step="0.01"
                                        required>

                                    @if (isset($compensationData['is_exceeded']) && $compensationData['is_exceeded'])
                                        <small class="form-text text-danger">จำนวนเงินเกินงบประมาณที่เหลืออยู่
                                            จึงถูกปรับลดให้เท่ากับงบประมาณคงเหลือ</small>
                                        <input type="hidden" name="is_adjusted" value="1">
                                    @endif
                                </div>

                                <div class="form-group mb-3">
                                    <label for="adjustment_reason">เหตุผลในการปรับยอด (ถ้ามี):</label>
                                    <textarea id="adjustment_reason" name="adjustment_reason" class="form-control" rows="2">{{ isset($compensationData['is_exceeded']) && $compensationData['is_exceeded'] ? 'งบประมาณคงเหลือไม่เพียงพอ' : '' }}</textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">บันทึกการเบิกจ่าย</button>
                                    <a href="{{ route('admin.ta.profile', $compensationData['student']->id) }}"
                                        class="btn btn-secondary">ยกเลิก</a>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> มีการเบิกจ่ายแล้ว</h5>
                                <p>ค่าตอบแทนของ {{ $compensationData['student']->name }} สำหรับเดือน
                                    {{ $compensationData['month_name'] }} {{ $compensationData['year'] }}
                                    ได้รับการเบิกจ่ายแล้ว</p>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#exportModal">
                                        <i class="fas fa-download"></i> ดาวน์โหลดเอกสารเบิกจ่าย
                                    </button>
                                    <a href="{{ route('admin.ta.profile', $compensationData['student']->id) }}"
                                        class="btn btn-secondary ml-2">
                                        <i class="fas fa-arrow-left"></i> กลับไปหน้าข้อมูลผู้ช่วยสอน
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับเลือกประเภทเอกสารที่ต้องการดาวน์โหลด -->
    @if ($isCompensated)
        <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">เลือกประเภทเอกสารเบิกจ่าย</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            <a href="{{ route('layout.exports.pdf', ['id' => $compensationData['student']->id, 'month' => $compensationData['year_month']]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <strong>แบบใบเบิกค่าตอบแทน (PDF)</strong>
                                    <p class="text-muted mb-0 small">เอกสารสำหรับการเบิกค่าตอบแทนประจำเดือน</p>
                                </div>
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="{{ route('layout.exports.result-pdf', ['id' => $compensationData['student']->id, 'month' => $compensationData['year_month']]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <strong>หลักฐานการจ่ายเงิน (PDF)</strong>
                                    <p class="text-muted mb-0 small">เอกสารหลักฐานการจ่ายเงินประจำเดือน</p>
                                </div>
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="{{ route('admin.export.template', ['id' => $compensationData['student']->id, 'month' => $compensationData['year_month']]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    <strong>ชุดเอกสารทั้งหมด (Excel)</strong>
                                    <p class="text-muted mb-0 small">รวมแบบใบเบิกและหลักฐานการจ่ายเงินในรูปแบบ Excel</p>
                                </div>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
