@extends('layouts.adminLayout')

@section('title', 'จัดการข้อมูลผู้ช่วยสอน')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <h4>จัดการข้อมูลผู้ช่วยสอน</h4>
                <div class="card mb-4 p-2">
                    <div class="card-body">
                        <h5 class="card-title">รายวิชาที่มีผู้ช่วยสอน</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัสวิชา</th>
                                    <th>ชื่อวิชา</th>
                                    <th>อาจารย์ผู้สอน</th>
                                    <th>จำนวน TA ที่ได้รับอนุมัติ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coursesWithTAs as $index => $course)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $course->subjects->subject_id ?? 'N/A' }}</td>
                                    <td>{{ $course->subjects->name_en ?? 'N/A' }}</td>
                                    <td>
                                        @if($course->teachers)
                                            {{ $course->teachers->title_th }} {{ $course->teachers->firstname_th }} {{ $course->teachers->lastname_th }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $course->course_tas->count() }}</td>
                                    <td>
                                        <a href="{{ route('layout.admin.detailsByid', $course->id) }}" class="btn btn-primary btn-sm">
                                            รายละเอียดผู้ช่วยสอน
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">ไม่พบข้อมูลรายวิชาที่มีผู้ช่วยสอน</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection