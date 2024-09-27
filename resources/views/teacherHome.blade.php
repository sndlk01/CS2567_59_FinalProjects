@extends('layouts.teacherLayout')

@section('title', 'Teacher')
@section('break', 'คำร้องการสมัครผู้ช่วยสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4>คำร้องการสมัครผู้ช่วยสอน</h4>
                    <div class="container shadow-lg bg-body rounded p-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">รหัสนักศึกษา</th>
                                    <th scope="col">ชื่อ-นามสกุล</th>
                                    <th scope="col">รายวิชาที่สมัคร</th>
                                    <th scope="col">วันที่สมัคร</th>
                                    <th scope="col">สถานะการสมัคร</th>
                                    <th scope="col">วันที่อนุมัติ</th>
                                    <th scope="col">ความคิดเห็น</th>
                                    <th scope="col">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $index => $request)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $request->courseTas->student->student_id ?? 'N/A' }}</td>
                                        <td>
                                            @if ($request->courseTas && $request->courseTas->student)
                                                {{ $request->courseTas->student->fname }}
                                                {{ $request->courseTas->student->lname }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if ($request->courseTas && $request->courseTas->course && $request->courseTas->course->subjects)
                                                {{-- {{ $request->courseTas->course->subjects->subject_id }} --}}
                                                {{ $request->courseTas->course->subjects->name_en }}
                                            @else
                                                N/A
                                                <!-- เพิ่มบรรทัดนี้เพื่อ debug -->
                                                {{-- ({{ var_dump($request->courseTas) }}) --}}
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            @if ($request->status == 'a')
                                                <span class="badge bg-success">อนุมัติ</span>
                                            @elseif($request->status == 'w')
                                                <span class="badge bg-warning">รอดำเนินการ</span>
                                            @elseif($request->status == 'r')
                                                <span class="badge bg-danger">ไม่อนุมัติ</span>
                                            @else
                                                <span class="badge bg-secondary">เลือก</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->approved_at ? \Carbon\Carbon::parse($request->approved_at)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $request->comment ?? '-' }}</td>
                                        <td>
                                            <form action="{{ route('teacher.home') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="request_id" value="{{ $request->id }}">
                                                <select name="status" class="form-select mb-2">
                                                    <option value="a" {{ $request->status == 'a' ? 'selected' : '' }}>
                                                        อนุมัติ</option>
                                                    <option value="w" {{ $request->status == 'w' ? 'selected' : '' }}>
                                                        รอดำเนินการ</option>
                                                    <option value="r" {{ $request->status == 'r' ? 'selected' : '' }}>
                                                        ไม่อนุมัติ</option>
                                                </select>
                                                <input type="text" name="comment" class="form-control mb-2"
                                                    placeholder="ความคิดเห็น" value="{{ $request->comment }}">
                                                <button type="submit" class="btn btn-primary">บันทึก</button>
                                            </form>
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
@endsection
