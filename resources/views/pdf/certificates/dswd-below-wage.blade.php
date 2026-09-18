@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, a bonafide resident of {{ $purokFormatted }}, San Jose, Municipality of Talibon, Province of Bohol.
    </div>
    <div class="body-p">
        Based on our records and verification, the above-mentioned individual is a <strong>{{ strtoupper($occupation ?? '__________') }}</strong> with a monthly income of <strong>P{{ number_format($monthlyIncome ?? 0, 2) }}</strong> which falls under the category of a below minimum-wage earner as per the latest wage rates set by the <strong>Regional Tripartite Wages and Productivity Board-Central Visayas (RTWPB-7)</strong>.
    </div>
    <div class="body-p">
        Due to the continuous rise in the cost of basic commodities and services caused by high inflation, the beneficiary is experiencing financial hardship, making it difficult to sustain daily needs.
    </div>
    <div class="body-p">
        This certification is issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> for the purpose of applying for financial assistance from the Department of Social Welfare and Development (DSWD) and any other government agencies or institutions that may extend aid.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
