@extends('layouts.taLayout')

@section('title', 'teaching')
@section('break', 'ตารางรายวิชา')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>กลุ่ม</th>
                                    <th>เวลาเริ่มเรียน</th>
                                    <th>เวลาเลิกเรียน</th>
                                    <th>เวลาที่สอน(นาที)</th>
                                    <th>อาจารย์ประจำวิชา</th>
                                    <th>การปฏิบัติงาน</th>
                                    <th>รายละเอียด</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $previousClassTitle = null; // กำหนดตัวแปรชั่วคราว
                                @endphp
                                @foreach ($teachings as $teaching)
                                    @if ($previousClassTitle != $teaching->class->title)
                                        <tr>
                                            <td colspan="8" class="fw-bold">
                                                {{ $teaching->class->title }}
                                            </td>
                                        </tr>
                                        @php
                                            $previousClassTitle = $teaching->class->title; // อัปเดตตัวแปร
                                        @endphp
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td>{{ $teaching->start_time }}</td> <!-- เวลาเริ่มเรียน (start_time) -->
                                        <td>{{ $teaching->end_time }}</td> <!-- เวลาเลิกเรียน (end_time) -->
                                        <td>{{ $teaching->duration }}</td> <!-- เวลาที่สอน (duration) -->
                                        <td>{{ $teaching->teacher->position }}{{ $teaching->teacher->degree }}
                                            {{ $teaching->teacher->fname }} {{ $teaching->teacher->lname }}
                                        </td> <!-- อาจารย์ประจำวิชา -->
                                        <td><span class="badge bg-danger">เข้าปฏิบัติการสอน</span></td>
                                        <td></td>
                                        <td><a href="#" class="btn btn-outline-primary"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                ลงเวลา</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
