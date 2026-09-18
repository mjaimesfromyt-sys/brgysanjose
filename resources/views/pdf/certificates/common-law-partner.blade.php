@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $partner = $requestModel->subject_name ?: '__________';
    $years = $requestModel->solo_parent_since ?: '__';
@endphp
    <div class="body-p">
        This is to certify that as per records available in this office, <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, a Filipino citizen, is a bona fide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        This is to certify further that <strong>{{ strtoupper($resident->name) }}</strong> is a common-law partner of <strong>{{ strtoupper($partner) }}</strong> and they have been living together for {{ $years }} years.
    </div>
    <div class="body-p">
        This certification is issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> for whatever legal purpose/s this may serve her/him best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
