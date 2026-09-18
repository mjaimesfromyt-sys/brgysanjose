@extends('pdf.layouts.letterhead')
@section('title', 'C E R T I F I C A T I O N')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $organizer = $requestModel->land_owner ?: '__________';
    $activityDate = $requestModel->date_died ? \Carbon\Carbon::parse($requestModel->date_died)->format('jS \d\a\y \o\f F, Y') : '__________';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, a resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol has participated in the <strong>TREE PLANTING ACTIVITY</strong> initiated by the <strong>{{ strtoupper($organizer) }}</strong> this {{ $activityDate }} held at Barangay San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        This Barangay Certification is being issued upon the request above mentioned name to support his/her graduation requirements.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
