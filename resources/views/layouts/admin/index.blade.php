@extends('layouts.adminLayout')

@section('content')
    <div class="container mt-4">
        @if (session()->has('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">จัดการประกาศ</h4>
                <div class="d-flex justify-content-start mb-4">
                    <a href="{{ route('announces.create') }}" class="btn btn-primary">เพิ่มประกาศ</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>หัวข้อประกาศ</th>
                                <th>ภาคการศึกษา</th>
                                <th>สถานะ</th>
                                <th>วันที่ประกาศ</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($announces as $announce)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $announce->title }}</td>
                                    <td>
                                        ปีการศึกษา {{ $announce->semester->year }}
                                        เทอม {{ $announce->semester->semesters }}
                                    </td>
                                    <td>
                                        @if ($announce->is_active)
                                            <span class="badge bg-success">เปิดใช้งาน</span>
                                        @else
                                            <span class="badge bg-danger">ปิดใช้งาน</span>
                                        @endif
                                    </td>
                                    <td>{{ $announce->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('announces.show', $announce->id) }}"
                                                class="btn btn-info btn-sm">
                                                ดู
                                            </a>
                                            <a href="{{ route('announces.edit', $announce->id) }}"
                                                class="btn btn-primary btn-sm">
                                                แก้ไข
                                            </a>
                                            <form action="{{ route('announces.destroy', $announce->id) }}" method="POST"
                                                onsubmit="return confirm('ต้องการลบประกาศนี้ใช่หรือไม่?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {!! $announces->links() !!}
        </div>
    </div>
@endsection
