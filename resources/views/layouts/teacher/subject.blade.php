@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'ข้อมูลรายวิชา')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <h4>รายวิชาทั้งหมดที่สอน</h4>
                <div class="container shadow-lg bg-body rounded p-5">
                    @if(empty($subjects))
                        <p>ไม่พบรายวิชาที่สอน</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">รหัสวิชา</th>
                                    <th scope="col">ชื่อวิชา</th>
                                    <th scope="col">จำนวนกลุ่มเรียน</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjects as $index => $subject)
                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ $subject['subject_id'] }}</td>
                                    <td>{{ $subject['name_en'] }}</td>
                                    <td>{{ count($subject['courses']) }}</td>
                                    <td>
                                        <a class="fw-bold" href="{{ url('/subject/subjectDetail/' . $subject['subject_id']) }}">รายละเอียดผู้ช่วยสอน</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection