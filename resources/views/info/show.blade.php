@extends('layouts.app')
@section('title', $transactionType->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <a href="{{ route('info.index') }}" class="text-decoration-none small fw-semibold">&larr; All transactions</a>

        <div class="card-soft p-4 mt-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="page-title">{{ $transactionType->name }}</h1>
                    <p class="page-subtitle mb-0">{{ $transactionType->description }}</p>
                </div>

                <div class="text-end">
                    @if ($transactionType->requires_residency)
                        <span class="pill pill--info">Residents only</span>
                    @else
                        <span class="pill pill--neutral">Open to all</span>
                    @endif

                    @if (! is_null($transactionType->fee))
                        <div class="mt-2 fw-bold" style="font-size:1.15rem">
                            @if ($transactionType->fee > 0)
                                ₱{{ number_format($transactionType->fee, 2) }}
                            @else
                                <span class="text-success">Free</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                Required documents
            </h2>

            <ul class="list-unstyled m-0">
                @forelse ($transactionType->requirements as $req)
                    <li class="d-flex gap-3 py-3 border-bottom">
                        <span class="text-success fw-bold flex-shrink-0" aria-hidden="true">&check;</span>
                        <span>{{ $req->item }}</span>
                    </li>
                @empty
                    <li class="text-muted py-3">No requirements listed.</li>
                @endforelse
            </ul>

            @if ($transactionType->requires_residency)
                <div class="alert alert-info mt-4 mb-0">
                    This transaction is available to <strong>verified barangay residents</strong> only.
                </div>
            @endif

            @auth
                <a href="{{ route('requests.create') }}" class="btn btn-primary btn-lg w-100 mt-4">
                    Request this document
                </a>
            @endauth
        </div>

    </div>
</div>
@endsection
