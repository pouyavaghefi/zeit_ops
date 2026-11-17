@extends('panel.layouts.master')

@section('wrapper')

    <div class="dashboard-content p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>All Websites</h2>

            <a href="{{ route('panel.websites.create') }}"
               class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Add New Website
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @forelse($websites as $website)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h5 class="mb-1">{{ $website->domain }}</h5>

                        <small class="text-muted">
                            {{ strtoupper($website->stack) }}
                            · {{ $website->framework }}
                            · {{ $website->runtime_version }}
                        </small>
                    </div>

                    <span class="badge bg-warning text-dark">
                    {{ ucwords(str_replace('_', ' ', $website->status)) }}
                </span>

                </div>
            </div>
        @empty
            <div class="alert alert-info">
                No websites created yet.
            </div>
        @endforelse

    </div>

@endsection
