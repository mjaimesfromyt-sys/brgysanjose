@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATE OF ATTESTATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        This is to certify that Mr./Mrs. <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, residing at {{ $purokFormatted }}, San Jose, Talibon, Bohol is a <strong>{{ strtoupper($occupation ?? '__________') }}</strong>, earning a monthly income of <strong>P{{ number_format($monthlyIncome ?? 0, 2) }}</strong>.
    </div>
    <div class="body-p">
        Following a thorough assessment and validation of the client's socio-economic profile conducted by the undersigned barangay official, it has been determined that Mr./Mrs. <strong>{{ strtoupper($resident->name) }}</strong> is an individual receiving income below the regional minimum wage and is facing significant financial challenges because of the effects of inflation, like the rising price of goods and services. The above-mentioned income remains insufficient to meet the unforeseen expenses on top of the family's monthly household expenses, thus further straining their limited financial resources.
    </div>
    <div class="body-p">
        This certification is issued upon the request of the above-named person for whatever legal purposes it may serve.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
