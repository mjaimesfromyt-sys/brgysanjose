@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE OF RESIDENCY')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $deceased = $requestModel->subject_name ?: ($requestModel->deceased_name ?: '__________');
    $child = $requestModel->requester_name ?: $resident->name;
    $diedOn = $requestModel->date_died ? \Carbon\Carbon::parse($requestModel->date_died)->format('F d, Y') : '__________';
    $placeOfDeath = $requestModel->burial_place ?: '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay, <strong>{{ strtoupper($deceased) }}</strong>, Filipino, of legal age, parent of <strong>{{ strtoupper($child) }}</strong>, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines, who died on <strong>{{ $diedOn }}</strong> at <strong>{{ strtoupper($placeOfDeath) }}</strong>.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER THAT <strong>{{ strtoupper($child) }}</strong> is the biological child of <strong>{{ strtoupper($deceased) }}</strong>.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above named person for whatever legal intent/s and purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
