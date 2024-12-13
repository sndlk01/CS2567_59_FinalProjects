@extends('layouts.taLayout')

@section('title', 'subjectList')
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
                                        <td>section</td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($courseTaClasses as $index => $courseTaClass)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $courseTaClass->courseTa->course->subjects->subject_id }}</td>
                                            <td>{{ $courseTaClass->courseTa->course->subjects->name_en }}</td>
                                            <td>{{ $courseTaClass->courseTa->course->semesters->semesters }}/{{ $courseTaClass->courseTa->course->semesters->year }}
                                            </td>
                                            <td>{{ $courseTaClass->courseTa->course->teachers->position }}
                                                {{ $courseTaClass->courseTa->course->teachers->degree }}
                                                {{ $courseTaClass->courseTa->course->teachers->name }}
                                                </td>
                                            <td>{{ $courseTaClass->courseTa->course->curriculums->name_th }}</td>
                                            <td>{{ $courseTaClass->class->section_num }}</td>
                                            <td>
                                                {{-- <a href="{{ route('layout.ta.attendances', ['id' => $courseTaClass->courseTa->id]) }}">รายละเอียดวิชา</a> --}}
                                                <a
                                                    href="{{ route('course_ta.show', ['id' => $courseTaClass->course_ta_id, 'classId' => $courseTaClass->class_id]) }}">
                                                    รายละเอียดวิชา
                                                </a>
                                            </td>
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
