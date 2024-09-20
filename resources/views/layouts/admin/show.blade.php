@extends('layouts.adminLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">รายละเอียดประกาศ</h4>

                        <form>
                            <div class="mb-3">
                                <label for="title" class="form-label">ชื่อประกาศ</label>
                                <input type="text" class="form-control bg-light" id="title"
                                    value="{{ $announce->title }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">รายละเอียด</label>
                                <textarea class="form-control bg-light" id="description" rows="3" readonly>{{ $announce->description }}</textarea>
                            </div>

                            <div class="d-flex justify-content-center">
                                <a href="{{ route('announces.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
