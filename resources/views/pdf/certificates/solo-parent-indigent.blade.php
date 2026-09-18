@extends('pdf.layouts.letterhead')
@section('title', 'SOLO PARENT INDIGENT')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, a Filipino citizen, and a solo parent, is a bonafide resident in {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    @if (!empty($occupation) && !empty($monthlyIncome))
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that <strong>{{ strtoupper($resident->name) }}</strong> is a <strong>{{ strtoupper($occupation) }}</strong> with a monthly income of <strong>{{ $monthlyIncomeWords }} (P{{ number_format($monthlyIncome, 2) }})</strong>.
    </div>
    @endif
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> for <strong>SOLO PARENTS REQUIREMENTS</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
