@extends('layouts.app')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h4 text-primary-light">{{ $pageTitle }}</h1>
            <div>
                <a href="{{ route('home') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light">/ Communication / {{ $pageTitle }}</span>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <span class="badge bg-primary-50 text-primary-600 mb-12">Illimi Communication</span>
                            <h5 class="mb-12">{{ $pageTitle }}</h5>
                            <p class="text-secondary-light mb-0">{{ $pageDescription }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-8">
                            <span class="badge bg-success-focus text-success-main">Web route ready</span>
                            <span class="badge bg-info-focus text-info-main">Package view loaded</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($highlights as $highlight)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="w-48-px h-48-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center mb-16">
                            <i class="ri-checkbox-circle-line"></i>
                        </div>
                        <h6 class="mb-8">{{ $highlight }}</h6>
                        <p class="text-secondary-light mb-0">
                            This page is now connected through the package web layer and ready for data-backed UI work.
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
