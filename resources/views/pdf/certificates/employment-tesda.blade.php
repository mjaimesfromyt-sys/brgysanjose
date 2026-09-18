@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CLEARANCE')
@section('body')
@php
    $ageDisplay = !empty($residentAge) ? $residentAge : 'of legal age';
    $statusDisplay = !empty($civilStatus) ? strtolower($civilStatus) : 'single';
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'TESDA REQUIREMENT';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, {{ $statusDisplay }}, a bonafide resident of San Jose, Talibon, Bohol, was connected with our firm as <strong>{{ strtoupper($requestModel->occupation ?? '__________') }}</strong>{{ $requestModel->employed_since ? ' since ' . $requestModel->employed_since : '' }} until now.
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
