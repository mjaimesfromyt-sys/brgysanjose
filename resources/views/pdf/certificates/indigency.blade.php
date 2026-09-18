@extends('pdf.layouts.letterhead')

@section('title', 'CERTIFICATE OF INDIGENCY')

@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok')
        ? $resident->purok
        : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay that <strong>{{ strtoupper($resident->name) }}</strong>, a Filipino citizen, {{ $ageDisplay }}, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>

    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that he/she is known to me of good moral character and is a law abiding citizen. He/she has neither pending case nor derogatory record in our office.
    </div>

    <div class="body-p">
        THIS IS TO CERTIFY FURTHERMORE that as per family census of records filed in the barangay, his/her family is one of those who belong to a low income family or <strong><em>"INDIGENT"</em></strong>.
    </div>

    <div class="body-p">
        This certification is being issued upon the request for the above mentioned-named person for whatever legal purpose/s it may serve him/her best.
    </div>

    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection

@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
