@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATE OF INDIGENCY')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $father = $requestModel->land_owner ?: '__________';
    $mother = $requestModel->owner_spouse ?: '__________';
    $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'SCHOLARSHIP REQUIREMENTS';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, {{ $ageDisplay }}, a Filipino citizen, son/daughter of Mr./Mrs. <strong>{{ strtoupper($father) }}</strong> and <strong>{{ strtoupper($mother) }}</strong>, is a bonafide resident in {{ $purokFormatted }}, San Jose, Talibon, Bohol.
    </div>
    @if (!empty($occupation) && !empty($monthlyIncome))
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that <strong>{{ strtoupper($father) }}</strong> is a <strong>{{ strtoupper($occupation) }}</strong> with monthly income of <strong>(P{{ number_format($monthlyIncome, 2) }})</strong>.
    </div>
    @endif
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($resident->name) }}</strong> to support of his/her <strong>{{ $purposeText }}</strong>.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
