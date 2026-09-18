@extends('pdf.layouts.letterhead')
@section('title', 'SOLO PARENT CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $soloSince = $requestModel->solo_parent_since ?? null;
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records in this barangay, <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }} and is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER THAT <strong>{{ strtoupper($resident->name) }}</strong> is a solo parent{{ $soloSince ? ' since ' . $soloSince : '' }} up to the present and was person of good moral character and has no derogatory and/or criminal records in this barangay.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above named for whatever legal purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-d')
@endsection
