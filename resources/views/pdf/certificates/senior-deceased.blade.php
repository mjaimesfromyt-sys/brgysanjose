@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $subject = $requestModel->subject_name ?: ($requestModel->deceased_name ?: $resident->name);
    $requester = $requestModel->requester_name ?: $resident->name;
    $diedOn = $requestModel->date_died ? \Carbon\Carbon::parse($requestModel->date_died)->format('F d, Y') : '__________';
@endphp
    <div class="body-p">
        This is to certify that <strong>{{ strtoupper($subject) }}</strong>, resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol died last <strong>{{ $diedOn }}</strong> and buried at <strong>{{ $requestModel->burial_place ?? '__________' }}</strong>.
    </div>
    <div class="body-p">
        This is to certify further that the above named person was a member of senior citizen with control No. <strong>{{ $requestModel->control_no ?? '__________' }}</strong> at San Jose, Talibon.
    </div>
    <div class="body-p">
        This Barangay Certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for whatever legal purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
