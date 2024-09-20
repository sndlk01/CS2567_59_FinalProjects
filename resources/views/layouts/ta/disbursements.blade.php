@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'อัปโหลดเอกสารการเบิกจ่ายผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <!-- <div class="card-header">{{ __('Admin') }}</div> -->
                <div class="card-body">
                    <div class="container">
                        <h4 class="mb-4">อัปโหลดเอกสารการเบิกจ่ายผู้ช่วยสอน</h4>

                        <form action="{{ route('layout.ta.disbursements') }}" method="post" enctype="multipart/form-data">

                            @csrf
                            {{-- <h5 class="mb-3">แบบฟอร์มกรอกรายละเอียดผู้ช่วยสอน</h5> --}}

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="applicant_type" id="newApplicant"
                                        value="0">
                                    <label class="form-check-label" for="newApplicant">
                                        ผู้สมัครรายใหม่
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="applicant_type" id="oldApplicant"
                                        value="1" checked>
                                    <label class="form-check-label" for="oldApplicant">
                                        ผู้สมัครรายเดิม
                                    </label>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="bookbank_id"
                                        placeholder="กรอกเลขบัญชีธนาคาร">
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="bank_name" aria-label="Default select example">
                                        <option selected>เลือกบัญชีธนาคาร</option>
                                        <option value="02 ธนาคารกรุงเทพ">02 ธนาคารกรุงเทพ</option>
                                        <option value="04 ธนาคารกสิกรไทย">04 ธนาคารกสิกรไทย</option>
                                        <option value="06 ธนาคารกรุงไทย">06 ธนาคารกรุงไทย</option>
                                        <option value="11 ธนาคารทหารไทยธนชาติ">11 ธนาคารทหารไทยธนชาติ</option>
                                        <option value="14 ธนาคารไทยพาณิชย์">14 ธนาคารไทยพาณิชย์</option>
                                        <option value="25 ธนาคารกรุงศรีอยุธยา">25 ธนาคารกรุงศรีอยุธยา</option>
                                        <option value="30 ธนาคารออมสิน">30 ธนาคารออมสิน</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="fileUpload" class="form-label ">อัปโหลดตารางเรียน แบบแจ้งข้อมูลหนี้บุลคลากร
                                    สำเนาบัตรประชาชน สำเนาบัญชีธนาคาร </label>
                                <input class="form-control" type="file" id="fileUpload" name="uploadfile">
                            </div>

                            <button type="submit" class="btn btn-success">ยืนยันการสมัคร</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
