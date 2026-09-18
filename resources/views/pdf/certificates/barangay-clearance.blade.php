@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CLEARANCE')
@section('salutation', 'To Whom It May Concern:')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
@endphp
    <div class="body-p">
        This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>, Filipino, legal age, is a resident of {{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above named person for the purpose of <strong>{{ $purposeText }}</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        <em>IN WITNESS WHEREOF,</em> we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
