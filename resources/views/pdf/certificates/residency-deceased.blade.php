@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE OF RESIDENCY')
@section('body')
@php
    $subject = $requestModel->subject_name ?: ($requestModel->deceased_name ?: $resident->name);
    $requester = $requestModel->requester_name ?: $resident->name;
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay, that <strong>{{ strtoupper($subject) }}</strong> is a resident of San Jose, Talibon, Bohol, and already passed away.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for <strong>{{ $purposeText }}</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
