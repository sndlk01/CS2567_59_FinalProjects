@extends('layouts.taLayout')

@section('title', 'teaching')
@section('break', 'ตารางรายวิชา')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                                    class="bi bi-x"></i></button>
                        </div>
                    @endif
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
                            {{-- <tbody>
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
                                            $previousClassTitle = $teaching->class->title;
                                        @endphp
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->start_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->end_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ $teaching->duration }}</td>
                                        <td>{{ $teaching->teacher->position }}{{ $teaching->teacher->degree }}
                                            {{ $teaching->teacher->name }}
                                        </td>
                                        <td>
                                            @if ($teaching->status === 'S')
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @else
                                                <span class="badge bg-danger">เข้าปฏิบัติการสอน</span>
                                            @endif
                                        </td>
                                        <td>{{ $teaching->attendance->note ?? '' }}</td>
                                        <td>
                                            <a href="{{ route('attendances.form', $teaching->id) }}"
                                                class="btn btn-outline-primary"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                ลงเวลา
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody> --}}
                            <tbody>
                                @php
                                    $previousClassTitle = null;
                                @endphp
                                @forelse ($teachings as $teaching)
                                    @if ($previousClassTitle != $teaching->class_id->title)
                                        <tr>
                                            <td colspan="8" class="fw-bold bg-light">
                                                {{ $teaching->class_id->title }}
                                            </td>
                                        </tr>
                                        @php
                                            $previousClassTitle = $teaching->class_id->title;
                                        @endphp
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->start_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($teaching->end_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ $teaching->duration }}</td>
                                        <td>
                                            {{ $teaching->teacher_id->position }}
                                            {{ $teaching->teacher_id->degree }}
                                            {{ $teaching->teacher_id->name }}
                                        </td>
                                        <td>
                                            {{-- @if ($teaching->status === 'S')
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @else
                                                <span class="badge bg-danger">เข้าปฏิบัติการสอน</span>
                                            @endif --}}
                                            {{-- @if ($teaching->status === 'S')
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @elseif ($teaching->status === 'L')
                                                <span class="badge bg-warning">ลา</span>
                                            @else
                                                <span class="badge bg-secondary">รอการลงเวลา</span>
                                            @endif --}}
                                            @if ($teaching->attendance && $teaching->attendance->status === 'เข้าปฏิบัติการสอน')
                                                <span class="badge bg-success">เข้าปฏิบัติการสอน</span>
                                            @elseif ($teaching->attendance && $teaching->attendance->status === 'ลา')
                                                <span class="badge bg-warning">ลา</span>
                                            @else
                                                <span class="badge bg-secondary">รอการลงเวลา</span>
                                            @endif
                                        </td>
                                        <td>{{ $teaching->attendance->note ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('attendances.form', $teaching->id) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                ลงเวลา
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">ไม่พบข้อมูลการสอน</td>
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
