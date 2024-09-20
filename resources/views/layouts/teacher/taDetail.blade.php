@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'รายวิชาทั้งหมด')
@section('break2', 'ข้อมูลรายวิชา')
@section('break3', 'ข้อมูลผู้ช่วยสอน')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <!-- <div class="card-header">{{ __('Admin') }}</div> -->
            <div class="container mt-4">

                <!-- <div class="card mb-4"> -->
                <div class="card-body">
                    <div class="row">
                        <h4 class="mb-4">ข้อมูลผู้ช่วยสอน</h4>

                        <div class="col-md-6">
                            <p><strong>ชื่อ - นามสกุล:</strong> ชาคริต ปรากฎ</p>
                            <p><strong>รหัสนักศึกษา:</strong> 643021316-6</p>
                            <p><strong>ระดับ:</strong> ปริญญาตรี</p>
                            <p><strong>เบอร์โทรศัพท์:</strong> 0812345678</p>
                            <p><strong>อีเมล:</strong> chakit.p@gmail.com</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>บัญชีธนาคาร:</strong> 04 ธนาคารกสิกรไทย</p>
                            <p><strong>เลขที่บัญชีธนาคาร:</strong> 064-123-4567</p>
                            <p><strong>สำเนาบัตรประจำประชาชน:</strong> <a href="#" class="text-primary">click</a>
                            </p>
                            <p><strong>สำเนาหน้าบัญชีธนาคาร:</strong> <a href="#" class="text-primary">click</a></p>
                            <p><strong>แบบแจ้งข้อมูลเจ้าหนี้:</strong> <a href="#" class="text-primary">click</a></p>
                        </div>
                    </div>
                </div>
                <!-- </div> -->

                <div class="card-body">
                    <div class="row">
                        <h4 class="mb-4">ชั่วโมงการสอน</h4>
                        <div class="mb-3">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-expanded="false"> เลือกเดือน
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item" href="#">มิถุนายน</a></li>
                                    <li><a class="dropdown-item" href="#">กรกฎาคม</a></li>
                                    <li><a class="dropdown-item" href="#">สิงหาคม</a></li>
                                    <li><a class="dropdown-item" href="#">กันยายน</a></li>

                                </ul>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <td>วัน/เดือน/ปี</td>
                                        <td>เวลาที่ปฏิบัติงาน</td>
                                        <td>กลุ่มเรียน (ปกติ/พิเศษ)</td>
                                        <td>ชั่วโมงการสอน</td>
                                        <td>การสอน</td>
                                        <td>งานที่ปฏิบัติ</td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>13-08-2567</td>
                                        <td>16:00-17:00</td>
                                        <td>sec.3 พิเศษ</td>
                                        <td>1</td>
                                        <td>ปฏิบัติการ</td>
                                        <td><span class="badge bg-secondary">เตรียมแลป</span></td>
                                        <td><span class="badge bg-success">อนุมัติการเข้าสอน</span></td>
                                    </tr>
                                    <tr>
                                        <td>14-08-2567</td>
                                        <td>10:30-12:30</td>
                                        <td>sec.3 พิเศษ</td>
                                        <td>2</td>
                                        <td>ปฏิบัติการ</td>
                                        <td><span class="badge bg-secondary">ให้คำแนะนำแลป</span></td>
                                        <td><span class="badge bg-success">อนุมัติการเข้าสอน</span></td>
                                    </tr>
                                    <tr>
                                        <td>15-08-2567</td>
                                        <td>16:00-17:00</td>
                                        <td>sec.3 พิเศษ</td>
                                        <td>1</td>
                                        <td>ปฏิบัติการ</td>
                                        <td><span class="badge bg-secondary">เตรียมแลป</span></td>
                                        <td><span class="badge bg-success">อนุมัติการเข้าสอน</span></td>
                                    </tr>
                                    <tr>
                                        <td>16-08-2567</td>
                                        <td>15:00-17:00</td>
                                        <td>sec.3 พิเศษ</td>
                                        <td>2</td>
                                        <td>ปฏิบัติการ</td>
                                        <td><span class="badge bg-secondary">ให้คำแนะนำแลป</span></td>
                                        <td><span class="badge bg-success">อนุมัติการเข้าสอน</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </row>
                    </div>


                    <div class="mt-3">
                        <button class="btn btn-success">Export</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </row>
    @endsection