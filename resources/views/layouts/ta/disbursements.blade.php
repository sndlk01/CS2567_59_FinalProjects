@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'อัปโหลดเอกสารการเบิกจ่ายผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Upload Form Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container">
                        <h4 class="mb-4">อัปโหลดเอกสารการเบิกจ่ายผู้ช่วยสอน</h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('layout.ta.disbursements') }}" method="post" enctype="multipart/form-data">
                            @csrf
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
                                <label for="fileUpload" class="form-label">อัปโหลดตารางเรียน แบบแจ้งข้อมูลหนี้บุลคลากร
                                    สำเนาบัตรประชาชน สำเนาบัญชีธนาคาร </label>
                                <input class="form-control @error('uploadfile') is-invalid @enderror" type="file"
                                    id="fileUpload" name="uploadfile">
                                @error('uploadfile')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success">ยืนยันการสมัคร</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Document Display Card -->
            @if (isset($disbursement) && $disbursement->uploadfile)
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <h4 class="mb-4">เอกสารที่อัปโหลด</h4>


                            <div class="card mt-4">
                                {{-- <div class="card-body">
                                    <h5 class="card-title">เอกสารที่อัปโหลด</h5> --}}
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%; padding: 15px;">ประเภทผู้สมัคร</th>
                                                <th style="width: 15%; padding: 15px;">เลขบัญชีธนาคาร</th>
                                                <th style="width: 15%; padding: 15px;">ธนาคาร</th>
                                                <th style="width: 30%; padding: 15px;">ชื่อไฟล์</th>
                                                <th style="width: 15%; padding: 15px;">วันที่อัปโหลด</th>
                                                <th style="width: 15%; padding: 15px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding: 15px;">{{ $disbursement->applicant_type == 0 ? 'ผู้สมัครรายใหม่' : 'ผู้สมัครรายเดิม' }}
                                                </td>
                                                <td style="padding: 15px;">{{ $disbursement->bookbank_id }}</td>
                                                <td style="padding: 15px;">{{ $disbursement->bank_name }}</td>
                                                <td style="white-space: normal; word-break: break-word; padding: 15px;">
                                                    {{ basename($disbursement->uploadfile) }}
                                                </td>
                                                <td style="padding: 15px;">{{ $disbursement->created_at->format('d/m/Y H:i') }}</td>
                                                <td style="padding: 15px;">
                                                    <a href="{{ route('layout.ta.download-document', $disbursement->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        ดาวน์โหลด
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- </div> --}}
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
