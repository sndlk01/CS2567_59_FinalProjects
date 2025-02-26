@extends('layouts.taLayout')

@section('title', 'subjectDetail')
@section('break', 'เซคชั่นที่สอน')

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
                                    {{ $courseTaClass->class->semesters->semesters }} /
                                    {{ $courseTaClass->class->semesters->year }}
                                </p>
                                <p><strong>อาจารย์ประจำวิชา:</strong>
                                    {{ $courseTaClass->class->teachers->position }}{{ $courseTaClass->class->teachers->degree }}
                                    {{ $courseTaClass->class->teachers->name }}
                                <p><strong>ชื่อผู้ช่วยสอน:</strong> {{ $student->name }}</p>
                            </div>
                        </div>

                        <h4>ลงเวลาการสอน</h4>
                        <div class="card-body">
                            <div class="menu">
                                <form action="{{ route('layout.ta.teaching', ['id' => $courseTaClass->class->class_id]) }}" 
                                    method="GET" class="d-block">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary">+ ลงเวลา</button>
                                        {{-- <a href="#" class="btn btn-success">ดาวน์โฟลดเอกสารสรุปภาระงาน</a>
                                        <a href="#" class="btn btn-success">ดาวน์โหลดเอกสาร</a> --}}
                                    </div>
                                    <div class="dropdown">
                                        <select name="month" class="form-select" aria-label="Default select example">
                                            @forelse($months as $month)
                                                <option value="{{ $month['value'] }}">{{ $month['name'] }}</option>
                                            @empty
                                                <option value="" disabled>ไม่พบข้อมูลเดือน</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
