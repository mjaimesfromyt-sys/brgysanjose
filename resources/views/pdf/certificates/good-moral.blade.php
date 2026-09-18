@extends('pdf.layouts.letterhead')

@section('title', 'CERTIFICATION of GOOD MORAL')

@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok')
        ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $statusDisplay = !empty($civilStatus) ? ucfirst($civilStatus) : 'Single';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, Filipino, {{ $statusDisplay }}, is a resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>

    <div class="body-p">
        THIS is to certify further <strong>{{ strtoupper($resident->name) }}</strong>, is known to me of good moral character and is a law abiding citizen. He/She has neither any pending case nor derogatory record in our office.
    </div>

    <div class="body-p">
        This certification is being issued upon the request of the above mentioned-named person for whatever legal purpose it may serve.
    </div>

    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection

@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
