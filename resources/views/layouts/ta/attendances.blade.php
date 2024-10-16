@extends('layouts.taLayout')

@section('title', 'attendance')
@section('break', 'ลงเวลาการสอน')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="container mt-4">
                        <h4>ลงเวลาการสอน</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6>การเข้าสอน</h6>
                                {{-- <form action="{{ route('attendances.submit', $teaching->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3 form-check">
                                        <input type="radio" class="form-check-input" id="status1" name="status"
                                            value="เข้าปฏิบัติการสอน" required>
                                        <label class="form-check-label" for="status1">เข้าปฏิบัติการสอน</label>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="radio" class="form-check-input" id="status2" name="status"
                                            value="ลา" required>
                                        <label class="form-check-label" for="status2">ลา</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="note" class="form-label">งานที่ปฏิบัติ</label>
                                        <input type="text" class="form-control" id="note" name="note"
                                            placeholder="งานที่ปฏิบัติ">
                                    </div>
                                    <button type="submit" class="btn btn-primary">ส่งข้อมูล</button>
                                </form> --}}
                                <form action="{{ route('attendances.submit', $teaching->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3 form-check">
                                        <input type="radio" class="form-check-input" id="status1" name="status"
                                            value="เข้าปฏิบัติการสอน" required>
                                        <label class="form-check-label" for="status1">เข้าปฏิบัติการสอน</label>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="radio" class="form-check-input" id="status2" name="status"
                                            value="ลา" required>
                                        <label class="form-check-label" for="status2">ลา</label>
                                    </div>

                                    <div class="mb-3">
                                        <label for="note" class="form-label">งานที่ปฏิบัติ</label>
                                        <input type="text" class="form-control" id="note" name="note"
                                            placeholder="งานที่ปฏิบัติ" required>
                                    </div>

                                    <!-- แสดงข้อผิดพลาดเมื่อไม่ได้กรอก note -->
                                    @error('note')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror

                                    <button type="submit" class="btn btn-primary">ส่งข้อมูล</button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
