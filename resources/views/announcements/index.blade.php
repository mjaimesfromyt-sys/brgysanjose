@extends('layouts.app')
@section('title', 'Barangay News')

@section('content')
<style>
    body {
        background-color: #f8fafc !important;
    }

    /* 1. Subtle Background Watermark */
    .news-watermark {
        position: fixed;
        top: 55%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        height: 600px;
        background-image: url('{{ asset('images/barangay-seal.png') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.035;
        pointer-events: none;
        z-index: 0;
    }

    .news-content-wrapper {
        position: relative;
        z-index: 1;
    }

    /* 2. Official News Hero Header with Seal */
    .news-hero-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px 26px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .news-hero-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #166534, #22c55e, #3b82f6);
    }

    .brgy-seal-news {
        width: 74px;
        height: 74px;
        object-fit: contain;
        filter: drop-shadow(0 6px 12px rgba(22, 101, 52, 0.16));
        transition: transform 0.25s ease;
    }
    .brgy-seal-news:hover {
        transform: scale(1.08) rotate(3deg);
    }

    /* 3. News Items Cards with Elevation */
    .news-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4.5px solid #16a34a !important;
        border-radius: 16px;
        padding: 22px 20px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .news-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -4px rgba(0,0,0,0.08);
        border-color: #86efac;
    }
    .news-item__title {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 10px;
        margin-bottom: 8px;
        line-height: 1.35;
    }
    .news-item__excerpt {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 0;
    }
</style>

<!-- Subtle Watermark sa Background -->
<div class="news-watermark"></div>

<div class="news-content-wrapper">

    <!-- ================= NEWS HERO HEADER WITH SEAL ================= -->
    <div class="news-hero-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="font-size: 24px;">📢</span>
                    <h1 class="h4 fw-bold text-dark mb-0">Barangay News &amp; Advisories</h1>
                </div>
                <p class="text-muted small mb-0">Official announcements, public advisories, and notices from Barangay San Jose, Talibon, Bohol.</p>
            </div>

            <!-- Klaro & Opisyal nga Barangay San Jose Seal -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="text-end d-none d-md-block">
                    <div class="text-uppercase fw-bold text-success" style="font-size: 11px; letter-spacing: 0.8px;">Public Information</div>
                    <div class="text-dark fw-bold" style="font-size: 13.5px;">Barangay San Jose</div>
                    <div class="text-muted small" style="font-size: 12px;">Talibon, Bohol</div>
                </div>
                <img src="{{ asset('images/barangay-seal.png') }}" 
                     alt="Barangay San Jose Seal" 
                     class="brgy-seal-news"
                     title="Official Seal of Barangay San Jose, Talibon">
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    @if ($announcements->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div style="font-size: 40px; margin-bottom: 12px;">📰</div>
            <h5 class="fw-bold text-dark mb-1">No announcements yet</h5>
            <p class="text-muted small mb-0">Check back soon for official barangay news and community updates.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach ($announcements as $post)
                <div class="col-md-6">
                    <a class="news-item" href="{{ route('announcements.show', $post) }}">
                        <div>
                            <div class="news-item__meta d-flex flex-wrap align-items-center gap-2 text-muted small">
                                @if ($post->is_pinned)
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                        @include('partials.icon', ['name' => 'pin', 'size' => 12])
                                        <span>Pinned</span>
                                    </span>
                                @endif
                                @if ($post->category)
                                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold">{{ $post->category }}</span>
                                @endif
                                <span class="text-muted" style="font-size: 12px;">📅 {{ $post->display_date->format('M d, Y') }}</span>
                            </div>

                            <h2 class="news-item__title">{{ $post->title }}</h2>
                            <p class="news-item__excerpt">{{ Str::limit($post->body, 160) }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    @endif

</div>
@endsection