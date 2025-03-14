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
                                <textarea class="form-control ckeditor @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="10">{{ old('description') }}</textarea>
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

@push('scripts')
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    {{-- <script src="https://cdn.ckeditor.com/4.25.1/standard/ckeditor.js"></script> --}}
    {{-- <script src="https://cdn.ckeditor.com/4.25.1/full/ckeditor.js"></script> --}}
    <script>
        CKEDITOR.disableAutoInline = true;
        // ปิดคำเตือนเวอร์ชัน
        CKEDITOR.env.isCompatible = true;
        CKEDITOR.replace('description', {
            language: 'th',
            height: 300,
            toolbar: [{
                    name: 'clipboard',
                    items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                },
                {
                    name: 'editing',
                    items: ['Find', 'Replace', '-', 'SelectAll']
                },
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-',
                        'RemoveFormat'
                    ]
                },
                {
                    name: 'paragraph',
                    items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft',
                        'JustifyCenter', 'JustifyRight', 'JustifyBlock'
                    ]
                },
                {
                    name: 'links',
                    items: ['Link', 'Unlink']
                },
                {
                    name: 'insert',
                    items: ['Table', 'HorizontalRule', 'SpecialChar']
                },
                {
                    name: 'styles',
                    items: ['Styles', 'Format', 'Font', 'FontSize']
                },
                {
                    name: 'colors',
                    items: ['TextColor', 'BGColor']
                },
            ]
        });
    </script>
@endpush
