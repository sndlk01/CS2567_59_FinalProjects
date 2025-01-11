@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Profile</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('ta.profile.update') }}">

                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Prefix</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('prefix') is-invalid @enderror" 
                                    name="prefix" value="{{ old('prefix', $user->prefix) }}">
                                @error('prefix')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Card ID</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('card_id') is-invalid @enderror" 
                                    name="card_id" value="{{ old('card_id', $user->card_id) }}">
                                @error('card_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Phone</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                    name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Student ID</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                    name="student_id" value="{{ old('student_id', $user->student_id) }}">
                                @error('student_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Email</label>
                            <div class="col-md-6">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">New Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                    name="password">
                                <small class="form-text text-muted">Leave blank to keep current password</small>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Confirm New Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection