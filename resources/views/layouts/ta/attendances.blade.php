@extends('layouts.taLayout')

@section('title', 'attendance')
@section('break', 'ลงเวลาการสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container mt-4">
                        <h4>รายละเอียดวิชา</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <p><strong>ชื่อวิชา:</strong> {{ $courseTa->course->subjects->subject_id }}
                                    {{ $courseTa->course->subjects->name_en }}</p>
                                <p><strong>ปีการศึกษา:</strong> {{ $courseTa->course->semesters->id }}</p>
                                <p><strong>อาจารย์ประจำวิชา:</strong> {{ $courseTa->course->teachers->position }}
                                    {{ $courseTa->course->teachers->degree }} {{ $courseTa->course->teachers->fname }}
                                    {{ $courseTa->course->teachers->lname }}</p>
                                <p><strong>โครงการ:</strong> {{ $courseTa->course->major->name_th }}</p>
                                <p><strong>ชื่อผู้ช่วยสอน:</strong> {{ $student->fname }} {{ $student->lname }}</p>
                                <p><strong>หน่วยกิต:</strong> {{ $courseTa->course->subjects->credits }}</p>
                            </div>
                        </div>

                        <h4>ลงเวลาการสอน</h4>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="menu">
                                    <a href="#" class="btn btn-primary">+ ลงเวลา</a>
                                    <a href="#" class="btn btn-success">ดาวน์โฟลดเอกสารสรุปภาระงาน</a>
                                    <a href="#" class="btn btn-success">ดาวน์โหลดเอกสาร</a>
                                </div>
                                <div class="dropdown">
                                    <select name="month" class="form-select" aria-label="Default select example">
                                        <option value="volvo">มิถุนายน</option>
                                        <option value="saab">กรกฎาคม</option>
                                        <option value="mercedes">สิงหาคม</option>
                                        <option value="audi">กันยายน</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="table-responsive">
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
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
