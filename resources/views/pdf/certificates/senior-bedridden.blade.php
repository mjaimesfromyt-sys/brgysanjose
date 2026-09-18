@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $subject = $requestModel->subject_name ?: $resident->name;
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $requester = $requestModel->requester_name ?: $resident->name;
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay <strong>{{ strtoupper($subject) }}</strong> is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that the above-mentioned name was a member of senior citizen with control No. <strong>{{ $requestModel->control_no ?? '__________' }}</strong> at San Jose, Talibon, Bohol and was person of good moral character and has no derogatory and/or criminal records in this barangay.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for bedridden beneficiaries.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
