@extends('layouts.adminLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">แก้ไขประกาศ</h4>

                        <form
                            action="{{ isset($announce) ? route('announces.update', $announce->id) : route('announces.store') }}"
                            method="POST">
                            @csrf
                            @if (isset($announce))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label for="title" class="form-label">ชื่อประกาศ</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="title" name="title" value="{{ old('title', $announce->title ?? '') }}"
                                    placeholder="กรอกชื่อประกาศ">
                                @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">รายละเอียด</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description', $announce->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('announces.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
                                <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
