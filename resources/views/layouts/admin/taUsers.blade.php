@extends('layouts.adminLayout')

@section('title', 'announce')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <!-- <div class="card-header">{{ __('Admin') }}</div> -->
            <div class="card-body">
                <h4>
                    จัดการข้อมูลผู้ช่วยสอน
                </h4>
                <div class="card mb-4 p-2">
                    <!-- <h5 >รายวิชาทั้งหมด</h5> -->
                    <div class="card-body">
                        <h5 class="card-title">รายวิชาทั้งหมด</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>ลำดับ</td>
                                    <td>รหัสวิชา</td>
                                    <td>ชื่อวิชา</td>
                                    <td>อาจารย์ผู้สอน</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>SC312003</td>
                                    <td>Dabase Management System and Database Design</td>
                                    <td>ผศ.ดร.พุธษดี ศิริแสงตระกูล</td>
                                    <td><a href="/admin/detailsta">รายละเอียดผู้ช่วยสอน</a></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>SC313004</td>
                                    <td>Software Engineering</td>
                                    <td>ผศ.ดร.ชิตสุธา สุ่มเล็ก</td>
                                    <td><a href="">รายละเอียดผู้ช่วยสอน</a></td>
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