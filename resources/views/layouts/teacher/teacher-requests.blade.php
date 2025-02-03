@extends('layouts.teacherLayout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                    <h4>แบบฟอร์มขอผู้ช่วยสอน</h4>

                    <form method="POST" action="{{ route('request.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">วิชา/กลุ่มเรียน</label>
                                    <select name="class_id" class="form-select" required>
                                        @if (isset($classes))
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->class_id }}">
                                                    {{ $class->course->subject->subject_id }} -
                                                    {{ $class->course->subject->name_th }} 
                                                    (Section {{ $class->section_num }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">ประเภทการเบิก</label>
                                    <select name="payment_type" class="form-select" required>
                                        <option value="lecture">เบิกเฉพาะบรรยาย (Lec.)</option>
                                        <option value="lab">เบิกเฉพาะปฏิบัติการ (Lab.)</option>
                                        <option value="both">เบิกทั้งบรรยายและปฏิบัติการ (Lec.+Lab)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="ta-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>รหัสนักศึกษา</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เบอร์โทร</th>
                                        <th>ระดับการศึกษา</th>
                                        <th>ชั่วโมง/สัปดาห์</th>
                                        <th>ชั่วโมงบรรยาย</th>
                                        <th>ชั่วโมงแลป</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="details[0][student_code]" class="form-control" required></td>
                                        <td><input type="text" name="details[0][name]" class="form-control" required></td>
                                        <td><input type="tel" name="details[0][phone]" class="form-control" required></td>
                                        <td>
                                            <select name="details[0][education_level]" class="form-select" required>
                                                <option value="bachelor">ป.ตรี</option>
                                                <option value="master">ป.โท</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="details[0][total_hours_per_week]" class="form-control" required></td>
                                        <td><input type="number" name="details[0][lecture_hours]" class="form-control" required></td>
                                        <td><input type="number" name="details[0][lab_hours]" class="form-control" required></td>
                                        <td><button type="button" class="btn btn-danger btn-sm delete-row" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" onclick="addRow()" class="btn btn-primary mb-4">
                            <i class="fas fa-plus"></i> เพิ่มแถว
                        </button>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> บันทึก
                            </button>
                            <a href="{{ route('request.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@push('scripts')
<script>
let rowIndex = 1;

function addRow() {
    const tbody = document.querySelector('#ta-table tbody');
    const newRow = `
        <tr>
            <td><input type="text" name="details[${rowIndex}][student_code]" class="form-control" required></td>
            <td><input type="text" name="details[${rowIndex}][name]" class="form-control" required></td>
            <td><input type="tel" name="details[${rowIndex}][phone]" class="form-control" required></td>
            <td>
                <select name="details[${rowIndex}][education_level]" class="form-select" required>
                    <option value="bachelor">ป.ตรี</option>
                    <option value="master">ป.โท</option>
                </select>
            </td>
            <td><input type="number" name="details[${rowIndex}][total_hours_per_week]" class="form-control" required></td>
            <td><input type="number" name="details[${rowIndex}][lecture_hours]" class="form-control" required></td>
            <td><input type="number" name="details[${rowIndex}][lab_hours]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger btn-sm delete-row" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', newRow);
    rowIndex++;
}

// Form validation
(() => {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
@endpush

@push('styles')
<style>
.table > :not(caption) > * > * {
    padding: 0.5rem;
}
.form-control, .form-select {
    padding: 0.375rem 0.75rem;
}
</style>
@endpush
@endsection