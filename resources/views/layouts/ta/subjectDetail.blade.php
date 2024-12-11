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
                                <p><strong>ชื่อวิชา:</strong> {{ $courseTaClass->class->course->subjects->subject_id }}
                                    {{ $courseTaClass->class->course->subjects->name_en }}</p>
                                <p><strong>ปีการศึกษา:</strong>
                                    {{ $courseTaClass->class->semesters->semesters }} / {{ $courseTaClass->class->semesters->year }}
                                </p>
                                <p><strong>อาจารย์ประจำวิชา:</strong>
                                    {{ $courseTaClass->class->teachers->position }}{{ $courseTaClass->class->teachers->degree }}
                                    {{ $courseTaClass->class->teachers->name }}
                                <p><strong>ชื่อผู้ช่วยสอน:</strong> {{ $student->name }}</p>
                                {{-- <p><strong>หน่วยกิต:</strong> {{ $courseTaClass->class->course->subjects->credits }}</p> --}}
                                {{-- <p><strong>Section:</strong> {{ $courseTaClass->class->section_num }}</p> --}}
                            </div>
                        </div>

                        <h4>ลงเวลาการสอน</h4>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="menu">
                                    <a href="{{ route('layout.ta.teaching', ['id' => $courseTaClass->class->id]) }}" class="btn btn-primary">+ ลงเวลา</a>
                                    <a href="#" class="btn btn-success">ดาวน์โฟลดเอกสารสรุปภาระงาน</a>
                                    <a href="#" class="btn btn-success">ดาวน์โหลดเอกสาร</a>
                                </div>
                                <div class="dropdown">
                                    <select name="month" class="form-select" aria-label="Default select example">
                                        <option value="Jun">มิถุนายน</option>
                                        <option value="Jul">กรกฎาคม</option>
                                        <option value="Aug">สิงหาคม</option>
                                        <option value="Sept">กันยายน</option>
                                        <option value="Oct">ตุลาคม</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
