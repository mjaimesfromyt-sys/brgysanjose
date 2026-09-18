@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('salutation', 'To Whom It May Concern:')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $requester = $requestModel->requester_name ?: $resident->name;
    $deceased = $requestModel->subject_name ?: ($requestModel->deceased_name ?: '__________');
    $heirsRaw = $requestModel->tenant_name ?? '';
    $heirs = array_filter(array_map('trim', explode(',', $heirsRaw)));
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that the parcel of land designated as <strong>Lot No. {{ $requestModel->lot_no ?? '__________' }}</strong> under <strong>Tax Dec. No. {{ $requestModel->tax_dec_no ?? '__________' }}</strong> containing an area of <strong>{{ $requestModel->land_area ?? '__________' }}</strong> situated in {{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol was originally owned by the late <strong>{{ strtoupper($deceased) }}</strong>.
    </div>

    @if (count($heirs) > 0)
    <div class="body-p">
        THIS CERTIFIES FURTHER that the said parcel of land has been subdivided among the following heirs:
    </div>
    <table style="width: 90%; margin: 4pt auto 8pt auto; font-size: 9.5pt; border-collapse: collapse;">
        @foreach ($heirs as $i => $heir)
        <tr>
            <td style="width: 8%; padding: 2pt 4pt; vertical-align: top;">{{ $i + 1 }}.</td>
            <td style="padding: 2pt 4pt; font-weight: bold;">{{ strtoupper($heir) }}</td>
        </tr>
        @endforeach
    </table>
    @endif

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
