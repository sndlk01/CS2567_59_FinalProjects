@extends('layouts.taLayout')

@section('title', 'Teaching Assistant')
@section('break', 'ประกาศต่างๆ')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="card bg-primary text-white p-3">
                    <h4 class="mb-0" style="color: white">ประกาศ</h4>
                </div>
                @foreach ($announces as $announce)
                <div class="card mt-3"> 
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-paperclip me-2"></i>
                                {{ $announce->title }}
                            </h5>
                            <p class="card-text">
                                {{ $announce->description }} <!-- Assuming content is plain text -->
                            </p>
                        </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
