@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $father = $requestModel->land_owner ?: '__________';
    $mother = $requestModel->owner_spouse ?: '__________';
    $birthdate = $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') : '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, Filipino, was born on <strong>{{ $birthdate }}</strong> at <strong>San Jose, Talibon, Bohol</strong>.
    </div>
    <div class="body-p">
        He/She is the son/daughter of Mr./Mrs. <strong>{{ strtoupper($father) }}</strong> and <strong>{{ strtoupper($mother) }}</strong>, bonafide residents of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        This certification is issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> for his/her <strong>LATE REGISTRATION</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
