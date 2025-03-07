@extends('layouts.adminLayout')

@section('title', 'แก้ไขอัตราค่าตอบแทน')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">แก้ไขอัตราค่าตอบแทน</h4>
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

                    <form action="{{ route('admin.compensation-rates.update', $rate->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">ประเภทการสอน</label>
                            <input type="text" class="form-control" readonly value="{{ $rate->teaching_type == 'regular' ? 'โครงการปกติ' : 'โครงการพิเศษ' }}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ประเภทคลาส</label>
                            <input type="text" class="form-control" readonly value="{{ $rate->class_type == 'LECTURE' ? 'บรรยาย' : 'ปฏิบัติการ' }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="rate_per_hour" class="form-label">อัตราต่อชั่วโมง (บาท) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" name="rate_per_hour" id="rate_per_hour" class="form-control" value="{{ $rate->rate_per_hour }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active" {{ $rate->status == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                                <option value="inactive" {{ $rate->status == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
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