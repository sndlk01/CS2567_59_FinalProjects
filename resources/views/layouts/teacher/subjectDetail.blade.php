@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'รายวิชาทั้งหมด')
@section('break2', 'ข้อมูลรายวิชา')

@section('content')
    {{-- <div class="container"> --}}
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>ข้อมูลรายวิชา</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        @foreach ($subjects as $subject)
                        <p><span class="fw-bold text-dark">ชื่อวิชา : </span>{{ $subject->subject_id }} {{ $subject->name_en }}</p>
                        @endforeach
                        <p><span class="fw-bold text-dark">ปีการศึกษา : </span>2/2567</p>
                        @foreach($teachers as $teacher)
                        <p><span class="fw-bold text-dark">อาจารย์ประจำวิชา : </span>{{ $teacher->position }} {{ $teacher->degree }} {{ $teacher->name }}</p>
                        @endforeach
                        <p><span class="fw-bold text-dark">หน่วยกิต : </span>3</p>
                    </div>
                </div>
                <div class="card-body">
                    <h4>ข้อมูลผู้ช่วยสอน</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">ชื่อ</th>
                                    <th scope="col">นามสกุล</th>
                                    <th scope="col">รหัสนักศึกษา</th>
                                    <th scope="col">ระดับ</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($students as $std)
                                    <th scope="row">1</th>
                                    <td>ชาคริต</td>
                                    <td>ปรากฏ</td>
                                    <td>643021316-6</td>
                                    @endforeach
                                    <td>ปริญญาตรี</td>
                                    <td><a class="fw-bold" href="{{ url('/subject/subjectDetail/taDetail') }}">ตรวจสอบข้อมูล</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- </div> --}}
@endsection
