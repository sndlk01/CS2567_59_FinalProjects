@extends('layouts.taLayout')

@section('title', 'attendance')
@section('break', 'ลงเวลาการสอน')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <!-- <div class="card-header">{{ __('Admin') }}</div> -->
            <div class="card-body">

                <div class="container mt-4">
                    <h4>รายละเอียดวิชา</h4>
                    <div class="card mb-4">
                        <div class="card-body">
                            <p><strong>ชื่อวิชา:</strong> SC312003 Dabase Management System and Database Design</p>
                            <p><strong>ปีการศึกษา:</strong> 2/2567</p>
                            <p><strong>อาจารย์ประจำวิชา:</strong> ผศ.ดร.พุธษดี ศิริแสงตระกูล</p>
                            <p><strong>โครงการ:</strong> โครงการพิเศษ</p>
                            <p><strong>กลุ่มการเรียน:</strong> 2</p>
                            <p><strong>ชื่อผู้ช่วยสอน:</strong> สุพัตรา แพงจันทร์</p>
                            <p><strong>หน่วยกิต:</strong> 3</p>
                        </div>
                    </div>

                    <h3>ลงเวลาการสอน</h3>
                    <div class="mb-3">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">เลือกเดือน
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
                                    <th>เวลาเริ่มเรียน</th>
                                    <th>เวลาเลิกเรียน</th>
                                    <th>เวลาที่สอน</th>
                                    <th>การเรียน</th>
                                    <th>การปฏิบัติงาน</th>
                                    <th>งานที่ปฏิบัติ</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>5-8-2024 10:30</td>
                                    <td>5-8-2024 12:30</td>
                                    <td>2 ชั่วโมง</td>
                                    <td>บรรยาย</td>
                                    <td><span class="badge bg-success">เข้าปฏิบัติการสอน</span></td>
                                    <td><span class="badge bg-secondary">สอนคาบบรรยาย</span></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>6-8-2024 08:30</td>
                                    <td>6-8-2024 10:30</td>
                                    <td>2 ชั่วโมง</td>
                                    <td>แลป</td>
                                    <td><span class="badge bg-danger">ไม่ได้เข้าปฏิบัติการสอน</span></td>
                                    <td><span class="badge bg-warning text-dark">ลา</span></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>7-8-2024 10:30</td>
                                    <td>7-8-2024 12:30</td>
                                    <td>2 ชั่วโมง</td>
                                    <td>บรรยาย</td>
                                    <td><span class="badge bg-danger">ไม่ได้เข้าปฏิบัติการสอน</span></td>
                                    <td><span class="badge bg-secondary">เลือก</span></td>
                                    <td>วันหยุด</td>
                                </tr>
                                <tr>
                                    <td>10-8-2024 10:30</td>
                                    <td>10-8-2024 12:30</td>
                                    <td>2 ชั่วโมง</td>
                                    <td>บรรยาย</td>
                                    <td><span class="badge bg-danger">ไม่ได้เข้าปฏิบัติการสอน</span></td>
                                    <td><span class="badge bg-secondary">เลือก</span></td>
                                    <td>สอนชดเชย วันที่ 7-2-2024</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection