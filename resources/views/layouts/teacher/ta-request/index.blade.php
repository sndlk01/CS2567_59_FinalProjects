@extends('layouts.teacherLayout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>รายชื่อวิชาที่สอน</h4>
                    <div class="container">
                        @if ($courses->isEmpty())
                            <div class="alert alert-info">
                                ไม่พบรายวิชาที่สอนในภาคการศึกษานี้
                            </div>
                        @else
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>รหัสวิชา</th>
                                                <th>ชื่อวิชา</th>
                                                <th>จำนวน TA</th>
                                                <th>รายชื่อผู้ช่วยสอน</th>
                                                <th>สถานะ</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($courses as $courseData)
                                            <tr>
                                                <td>{{ $courseData['course']->subjects->subject_id ?? 'N/A' }}</td>
                                                <td>{{ $courseData['course']->subjects->name_en ?? 'N/A' }}</td>
                                                <td>{{ $courseData['approved_tas']->count() }}</td>
                                                <td>
                                                    @if ($courseData['approved_tas']->isNotEmpty())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($courseData['approved_tas'] as $ta)
                                                                <li>{{ $ta->student->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($courseData['latest_request'])
                                                        @switch($courseData['latest_request']->status)
                                                            @case('W')
                                                                <span class="badge bg-success">ยื่นคำร้องแล้ว</span>
                                                                @break
                                                            @case('A')
                                                                <span class="badge bg-success">อนุมัติแล้ว</span>
                                                                @break
                                                            @case('R')
                                                                <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                                @break
                                                        @endswitch
                                                    @else
                                                        <span class="badge bg-secondary">ยังไม่ยื่นคำร้อง</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($courseData['approved_tas']->count() > 0 && (!$courseData['latest_request'] || $courseData['latest_request']->status === 'R') && $courseData['latest_request']?->status !== 'A')
                                                        <a href="{{ route('teacher.ta-requests.create', ['course_id' => $courseData['course']->course_id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                            ยื่นคำร้อง
                                                        </a>
                                                    @elseif ($courseData['approved_tas']->count() === 0)
                                                        <span class="text-muted">ไม่มี TA ในรายวิชานี้</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ประวัติคำร้อง -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">ประวัติคำร้องขอผู้ช่วยสอน</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>วันที่</th>
                                                <th>รายวิชา</th>
                                                <th>สถานะ</th>
                                                <th>จำนวน TA</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($requests as $request)
                                                <tr>
                                                    <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if ($request->course && $request->course->subjects)
                                                            {{ $request->course->subjects->name_en }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @switch($request->status)
                                                            @case('W')
                                                                <span class="badge bg-success">ยื่นคำร้องแล้ว</span>
                                                            @break

                                                            @case('A')
                                                                <span class="badge bg-success">อนุมัติ</span>
                                                            @break

                                                            @case('R')
                                                                <span class="badge bg-danger">ไม่อนุมัติ</span>
                                                            @break
                                                        @endswitch
                                                    </td>
                                                    <td>{{ $request->details->sum(function ($detail) {return $detail->students->count();}) }}
                                                    </td>
                                                    {{-- <td>
                                                        @if ($request->status === 'W')
                                                            <a href="{{ route('teacher.ta-requests.edit', $request->id) }}"
                                                                class="btn btn-primary px-4 me-2">
                                                                แก้ไขคำร้อง
                                                            </a>
                                                        @endif
                                                    </td> --}}
                                                    <td>
                                                        <a href="{{ route('teacher.ta-requests.show', $request->id) }}"
                                                            class="btn btn-primary btn-sm">
                                                            ดูรายละเอียด
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .table> :not(caption)>*>* {
                padding: 1rem 0.75rem;
            }

            .badge {
                font-size: 0.875em;
            }

            .list-unstyled {
                margin-bottom: 0;
            }
        </style>
    @endpush
@endsection