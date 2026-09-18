@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $requester = $requestModel->requester_name ?: $resident->name;
    $spousePart = $requestModel->owner_spouse ? ' married to <strong>' . strtoupper($requestModel->owner_spouse) . '</strong>' : '';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that the parcel of land designated as <strong>Lot No. {{ $requestModel->lot_no ?? '__________' }}</strong> with <strong>Tax. Dec. {{ $requestModel->tax_dec_no ?? '__________' }}</strong> with an area of <strong>{{ $requestModel->land_area ?? '__________' }}</strong> is owned by <strong>{{ strtoupper($requestModel->land_owner ?? '__________') }}</strong>@if($requestModel->owner_spouse) married to <strong>{{ strtoupper($requestModel->owner_spouse) }}</strong>@endif situated in {{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS CERTIFIES FURTHER that the said parcel of land is classified as agricultural land and is subject for reclassification of land into residential/commercial land as reflected in the approved Municipal Comprehensive Land Use Plan (CLUP) per SP-Resolution 2022-473 and Talibon Municipal Zoning Ordinance No. 2022-11 Series of 2022.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for whatever legal purpose and intent it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-c')
@endsection
