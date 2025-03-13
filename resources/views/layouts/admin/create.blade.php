@extends('layouts.adminLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ route('announces.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label">ชื่อประกาศ</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="title" name="title" value="{{ old('title') }}" placeholder="กรอกชื่อประกาศ">
                                @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">รายละเอียด</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="semester_id" class="form-label">เลือกภาคการศึกษา</label>
                                <select name="semester_id" id="semester_id"
                                    class="form-control @error('semester_id') is-invalid @enderror">
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->semester_id }}"
                                            {{ session('user_active_semester_id') == $semester->semester_id ? 'selected' : '' }}>
                                            ปีการศึกษา {{ $semester->year }} เทอม {{ $semester->semesters }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('semester_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    เปิดใช้งานประกาศ
                                </label>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('announces.index') }}" class="btn btn-secondary">ยกเลิก</a>
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
