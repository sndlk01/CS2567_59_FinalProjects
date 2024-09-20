@extends('layouts.taLayout')

@section('title', 'request')
@section('break', 'สถานะคำร้องการสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <!-- <div class="card-header">{{ __('Admin') }}</div> -->
                <div class="card-body">
                    <div class="container mt-4">
                        <h4 class="mb-3">คำร้องการสมัครผู้ช่วยสอน</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <td>ลำดับ</td>
                                        <td>รหัสนักศึกษา</td>
                                        <td>ชื่อ-นามสกุล</td>
                                        <td>รายวิชาที่สมัคร</td>
                                        <td>วันที่สมัคร</td>
                                        <td>สถานะการสมัคร</td>
                                        <td>วันที่อนุมัติ</td>
                                        <td>ความคิดเห็น</td>
                                    </tr>
                                    @foreach ($requests as $index => $request)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $request->courseTas->student->student_id }}</td>
                                            <td>{{ $request->courseTas->student->fname }}
                                                {{ $request->courseTas->student->lname }}</td>
                                            <td>{{ $request->courseTas->course->subjects->subject_id }}
                                                {{ $request->courseTas->course->subjects->name_en }}</td>
                                            <td>{{ $request->created_at ? $request->created_at->format('d-m-Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                @if ($request->status === 'W')
                                                    <span class="badge bg-warning">รอดำเนินการ</span>
                                                @elseif($request->status === 'N')
                                                    <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                @elseif($request->status === 'A')
                                                    <span class="badge bg-success">อนุมัติ</span>
                                                @else
                                                    <span class="badge bg-secondary">ไม่ระบุ</span>
                                                @endif
                                            </td>
                                            <td>{{$request->approve_at}}</td>
                                            <td>{{$request->comment}}</td>
                                        </tr>
                                    @endforeach
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
