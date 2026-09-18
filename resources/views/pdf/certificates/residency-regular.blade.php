@extends('pdf.layouts.letterhead')

@section('title', 'BARANGAY CERTIFICATE OF RESIDENCY')

@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok')
        ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay that <strong>{{ strtoupper($resident->name) }}</strong>, Filipino, of legal age, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>

    <div class="body-p">
        This is to certify further that the above-mentioned has stayed in the barangay and has not travelled outside Bohol for the 5 years.
    </div>

    <div class="body-p">
        This certification is being issued upon the request for the above mentioned-name person for whatever legal purpose/s it may serve him/her best.
    </div>

    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection

@section('signatories')
    @include('pdf.partials.sig-d')
@endsection
