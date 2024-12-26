@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'รายวิชาทั้งหมด')
@section('break2', 'ข้อมูลรายวิชา')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>ข้อมูลรายวิชา</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        <p><span class="fw-bold text-dark">ชื่อวิชา : </span> {{ $course['subject']['subject_id'] }} - {{ $course['subject']['name_th'] }}</p>
                        <p><span class="fw-bold text-dark">Course : </span> {{ $course['subject']['name_en'] }}</p>
                        <p><span class="fw-bold text-dark">ปีการศึกษา : </span>{{ $course['current_semester']['semester'] }}/{{ $course['current_semester']['year'] }}</p>
                        <p><span class="fw-bold text-dark">อาจารย์ประจำวิชา : </span>
                            @if(isset($course['teacher']))
                                {{ $course['teacher']['position'] ?? '' }} 
                                {{ $course['teacher']['degree'] ?? '' }} 
                                {{ $course['teacher']['name'] ?? '' }}
                            @else
                                -
                            @endif
                        </p>
                        <p><span class="fw-bold text-dark">หน่วยกิต : </span>{{ $course['subject']['credit'] ?? 3 }}</p>
                    </div>
                </div>
                <div class="card-body">
                    <h4>ข้อมูลผู้ช่วยสอน</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    
                                    {{-- <th scope="col">นามสกุล</th> --}}
                                    <th scope="col">รหัสนักศึกษา</th>
                                    <th scope="col">ชื่อ-นามสกุล</th>
                                    <th scope="col">อีเมล</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($course['teaching_assistants'] as $index => $ta)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        
                                        {{-- <td>{{ $ta['lastname'] }}</td> --}}
                                        <td>{{ $ta['student_id'] }}</td>
                                        <td>{{ $ta['name'] }}</td>
                                        <td>{{ $ta['email'] }}</td>
                                        <td>
                                            <a class="fw-bold" href="{{ url('/subject/subjectDetail/taDetail/' . $ta['id']) }}">
                                                ตรวจสอบข้อมูล
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">ไม่พบข้อมูลผู้ช่วยสอน</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection