@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
    $employer = $requestModel->land_owner ?: '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that <strong>{{ strtoupper($resident->name) }}</strong> is employed as <strong>{{ strtoupper($requestModel->occupation ?? '__________') }}</strong> of <strong>{{ strtoupper($employer) }}</strong>{{ $requestModel->employed_since ? ' since ' . $requestModel->employed_since : '' }}.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above named person for <strong>{{ $purposeText }}</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
