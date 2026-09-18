@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $prevAddress = $requestModel->purpose ?: '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay, <strong>{{ strtoupper($resident->name) }}</strong>, Filipino, of legal age, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER THAT he/she has officially moved out at <strong>{{ strtoupper($prevAddress) }}</strong> and transfer at {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above named person for whatever legal intent/s and purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-d')
@endsection
