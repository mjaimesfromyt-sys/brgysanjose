@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('salutation', 'To Whom It May Concern:')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>
    <div class="body-p">
        This is to certify further that <strong>{{ strtoupper($resident->name) }}</strong> is still alive.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above-named person for the purpose of: <strong><em>{{ $purposeText }}</em></strong>
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        <em>IN WITNESS WHEREOF, we have hereunto set our signatures this</em> <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-d')
@endsection
