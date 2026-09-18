@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $destination = $requestModel->purpose ?: '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that the above mentioned name and their family will transfer from San Jose, Talibon, Bohol, to <strong>{{ strtoupper($destination) }}</strong>.
    </div>
    <div class="body-p">
        This Certification is being issued upon the request of the above-named-person for whatever legal purpose and intent it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
