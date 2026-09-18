@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('salutation', 'To Whom It May Concern:')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $requester = $requestModel->requester_name ?: $resident->name;
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that the parcel of land designated as <strong>Lot No. {{ $requestModel->lot_no ?? '__________' }}</strong> under the <strong>Tax Dec. No. {{ $requestModel->tax_dec_no ?? '__________' }}</strong>, containing an area of <strong>{{ $requestModel->land_area ?? '__________' }}</strong> situated in {{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol owned by <strong>{{ strtoupper($requestModel->land_owner ?? '__________') }}</strong> has no adverse claims or any other conflicts regarding land dispute filed in this barangay.
    </div>
    <div class="body-p">
        This certification is being made to support for the approval of subdivision survey in DENR-7, Cebu and for whatever legal purpose and intent it may served it best.
    </div>
    <div class="body-p">
        This certificate is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for whatever legal purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
