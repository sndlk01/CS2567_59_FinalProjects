@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'ข้อมูลรายวิชา')

@section('content')
    {{-- <div class="container"> --}}
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                <h4>รายวิชาทั้งหมด</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">รหัสวิชา</th>
                                    <th scope="col">ชื่อวิชา</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($subjects as $subject)
                                    <th scope="row">{{ $subject->id }}</th>
                                    <td>{{ $subject->subject_id }}</td>
                                    <td>{{ $subject->name_en }}</td>
                                    @endforeach
                                    <td><a class="fw-bold" href="{{ url('/subject/subjectDetail') }}">รายละเอียดผู้ช่วยสอน</a></td>
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
