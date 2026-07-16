@extends('layouts.app')
@section('title', '500 Server Error')
@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="text-center">
        <h1 class="display-1 fw-bold">500</h1>
        <h2 class="h4">Internal Server Error</h2>
        <p class="text-muted">Oops, something went wrong on our servers.</p>
        <a href="{{ url('/') }}" class="btn btn-primary mt-3">Return to Home</a>
    </div>
</div>
@endsection
