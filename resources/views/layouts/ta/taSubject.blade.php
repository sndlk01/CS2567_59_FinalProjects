@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'ข้อมูลรายวิชาผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>
                        ข้อมูลรายวิชาผู้ช่วยสอน
                    </h4>
                    <div class="card-body">
                        <h5 class="card-title">รายวิชาทั้งหมด</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <td>ลำดับ</td>
                                        <td>รหัสวิชา</td>
                                        <td>รายวิชา</td>
                                        <td>ปีการศึกษา</td>
                                        <td>อาจารย์ประจำวิชา</td>
                                        <td>สาขา</td>
                                        <td>โครงการ</td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($courseTas as $index => $courseTa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $courseTa->course->subjects->subject_id }}</td>
                                            <td>{{ $courseTa->course->subjects->name_en }}</td>
                                            <td>{{ $courseTa->course->semesters->semesters }}/{{ $courseTa->course->semesters->year }}
                                            </td>
                                            <td>{{ $courseTa->course->teachers->position }}
                                                {{ $courseTa->course->teachers->degree }}
                                                {{ $courseTa->course->teachers->fname }}
                                                {{ $courseTa->course->teachers->lname }}</td>
                                            <td>{{ $courseTa->course->curriculums->name_th }}</td>
                                            <td>{{ $courseTa->course->major->name_th ?? 'ไม่มีข้อมูล' }}</td>
                                            <td><a href="/attendances/{{ $courseTa->id }}">ลงเวลา</a></td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
