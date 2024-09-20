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
                                <th>วันที่ประกาศ</th>
                                <th></th>
                            </tr>
                            @foreach ($announces as $announce)
                                <tr>
                                    <td class="col-1">{{ ++$i }}</td>
                                    <td class="col-2">{{ $announce->title }}</td>
                                    <td class="col-4">{{ $announce->created_at  }}</td>
                                    <td class="col-3">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('announces.show', $announce->id) }}"
                                                class="btn btn-info btn-sm">
                                                แสดงประกาศ
                                            </a>
                                            <a href="{{ route('announces.edit', $announce->id) }}"
                                                class="btn btn-primary btn-sm">
                                                แก้ไข
                                            </a>
                                            <form action="{{ route('announces.destroy', $announce->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {!! $announces->links() !!}
        </div>
    </div>
@endsection
