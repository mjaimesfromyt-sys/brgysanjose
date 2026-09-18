@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CLEARANCE (Fencing Permit)')
@section('salutation', '')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $spouse = $requestModel->owner_spouse ? ' MARRIED TO ' . strtoupper($requestModel->owner_spouse) : '';
@endphp
    <div class="body-p">
        This clearance is hereby granted to <strong>{{ strtoupper($resident->name) }}{!! $spouse !!}</strong> with residence address at {{ $purokFormatted }}, San Jose, Talibon, Bohol in connection with his/her application for residential building permit at {{ $purokFormatted }}, San Jose, Talibon, Bohol pursuant to the provisions under National Building Code (PD 1096) governing issuance of such Fencing construction or renovation permit.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        <em>IN WITNESS WHEREOF,</em> we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
