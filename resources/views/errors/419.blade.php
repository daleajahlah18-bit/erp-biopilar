@extends('layouts.app')
@section('title', '419 Page Expired')
@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="text-center">
        <h1 class="display-1 fw-bold">419</h1>
        <h2 class="h4">Page Expired</h2>
        <p class="text-muted">Your session has expired. Please refresh the page and try again.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Go Back</a>
    </div>
</div>
@endsection
