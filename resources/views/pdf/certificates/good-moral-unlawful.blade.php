@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $statusDisplay = !empty($civilStatus) ? ucfirst($civilStatus) : 'Single';
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE IT MAY SERVE';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, Filipino, {{ $statusDisplay }}, and a resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, has been known to this office and the undersigned Barangay Officials as a person of good moral character.
    </div>
    <div class="body-p">
        THIS is to certify further that based on available records and testimonies from the community that <strong>{{ strtoupper($resident->name) }}</strong> is not involved in any cult or any group engaged in unlawful activities.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> in connection with his/her application as a <strong>{{ $purposeText }}</strong> and for whatever legal purpose it may serve.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
