@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CLEARANCE')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>, is a bonafide resident of {{ $purokFormatted }}, San Jose, Talibon, Bohol, Philippines.
    </div>
    <div class="body-p">
        This certificate is being issued upon the request of the above-named mentioned for <strong>ELECTRICAL CONNECTION</strong> of his/her residential building located at {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        <em>IN WITNESS WHEREOF,</em> we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
