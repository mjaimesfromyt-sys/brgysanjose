@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION (Cutting Permit)')
@section('body')
@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
    $treeInfo = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'TREE/S';
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($resident->name) }}</strong>, of {{ $purokFormatted }}, San Jose, Talibon, Bohol, is requesting a Barangay Certification for the cutting of <strong>{{ $treeInfo }}</strong> located at Barangay San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        THIS IS TO CERTIFY FURTHER that the said tree has been inspected and found to be hazardous, and its condition poses a risk to public safety and property.
    </div>
    <div class="body-p">
        For this matter, the Sangguniang Barangay of San Jose INTERPOSES NO OBJECTION for the cutting of tree/s on the aforementioned vicinity.
    </div>
    <div class="body-p">
        This certification is being issued upon the request of the above mentioned name granting him/her the permission to cut the trees mentioned above and for whatever legal intents and purposes that this may serve.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
