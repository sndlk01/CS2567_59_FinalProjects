@extends('layouts.adminLayout')

@section('title', 'เพิ่มอัตราค่าตอบแทนใหม่')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">เพิ่มอัตราค่าตอบแทนใหม่</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.compensation-rates.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="teaching_type" class="form-label">ประเภทการสอน <span class="text-danger">*</span></label>
                            <select name="teaching_type" id="teaching_type" class="form-select" required>
                                <option value="regular">โครงการปกติ</option>
                                <option value="special">โครงการพิเศษ</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_type" class="form-label">ประเภทคลาส <span class="text-danger">*</span></label>
                            <select name="class_type" id="class_type" class="form-select" required>
                                <option value="LECTURE">บรรยาย</option>
                                <option value="LAB">ปฏิบัติการ</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rate_per_hour" class="form-label">อัตราต่อชั่วโมง (บาท) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" name="rate_per_hour" id="rate_per_hour" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active">ใช้งาน</option>
                                <option value="inactive">ไม่ใช้งาน</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            <a href="{{ route('admin.compensation-rates.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection