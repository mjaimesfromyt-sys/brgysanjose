@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE OF RESIDENCY')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay that <strong>{{ strtoupper($resident->name) }}</strong>, Filipino, of legal age, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines. And he/she is known to me of good moral character and is a law abiding citizen.
    </div>
    <div class="body-p">
        Further, this certifies that based on available records and testimonies from the community, <strong>{{ strtoupper($resident->name) }}</strong> is not involved in any cult or any group engaged in unlawful activities.
    </div>
    <div class="body-p">
        This certification is being issued upon the request for the above mentioned-named person for his/her <strong>{{ $purposeText }}</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
