@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION OF STATE OF CALAMITY')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that Mr./Mrs. <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, Filipino, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that <strong>{{ strtoupper($resident->name) }}</strong> was directly affected by Tropical Storm "Bagyong Odette". The Provincial Government declared the entire Province of Bohol under the state of calamity due to severe house damage brought about the Tropical Storm "Odette" that hit the entire Province on December 16, 2021.
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
