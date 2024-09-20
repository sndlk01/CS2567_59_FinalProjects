@extends('layouts.adminLayout')

@section('title', 'request')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <!-- <div class="card-header">{{ __('Admin') }}</div> -->
            <div class="card-body">
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลรายวิชา</h5>
                    <p class="card-text"><strong>ชื่อวิชา:</strong> SC312003 Dabase Management System and Database
                        Design</p>
                    <p class="card-text"><strong>ปีการศึกษา:</strong> 2/2567</p>
                    <p class="card-text"><strong>อาจารย์ประจำวิชา:</strong> ผศ.ดร.พุธษดี ศิริแสงตระกูล</p>
                    <p class="card-text"><strong>หน่วยกิต:</strong> 3</p>
                </div>
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลผู้ช่วยสอน</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>ลำดับ</td>
                                    <td>ชื่อ</td>
                                    <td>นามสกุล</td>
                                    <td>รหัสนักศึกษา</td>
                                    <td>ระดับ</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>ชาคริต</td>
                                    <td>ปรากฎ</td>
                                    <td>643021316-6</td>
                                    <td>ปริญญาตรี</td>
                                    <td><a href="/admin/detailsta/id">ตรวจสอบข้อมูล</a></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>สุพัตรา</td>
                                    <td>แพงจันทร์</td>
                                    <td>643021342-5</td>
                                    <td>ปริญญาตรี</td>
                                    <td><a href="">ตรวจสอบข้อมูล</a></td>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection