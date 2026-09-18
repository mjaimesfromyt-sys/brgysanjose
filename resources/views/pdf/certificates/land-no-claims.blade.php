@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('salutation', 'To Whom It May Concern:')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $requester = $requestModel->requester_name ?: $resident->name;
    $taxDecPart = $requestModel->tax_dec_no ? ' part of the <strong>Tax Declaration No. ' . $requestModel->tax_dec_no . '</strong>' : '';
    $cadPart = $requestModel->cad_no ? ' under the <strong>CAD No. ' . $requestModel->cad_no . '</strong>' : '';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that the parcel of land designated as <strong>Lot No. {{ $requestModel->lot_no ?? '__________' }}</strong>{!! $taxDecPart !!}{!! $cadPart !!} containing an area of <strong>{{ $requestModel->land_area ?? '__________' }}</strong> situated in {{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol owned by <strong>{{ strtoupper($requestModel->land_owner ?? '__________') }}</strong>.
    </div>
    <div class="body-p">
        THIS CERTIFIES FURTHERMORE that the said parcel of land is free from any adverse claims or any other conflicts regarding land dispute filed in this barangay.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for whatever legal purpose and intent it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        IN WITNESS WHEREOF, we have hereunto set our signatures this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-b')
@endsection
