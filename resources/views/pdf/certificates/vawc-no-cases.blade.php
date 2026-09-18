@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $requester = $requestModel->requester_name ?: $resident->name;
    $period = $requestModel->purpose ?: '__________';
@endphp
    <div class="body-p">
        This is to certify that based on official records of <strong>Barangay San Jose, Talibon, Bohol</strong>, there are no reported or recorded cases of <strong>Violence Against Women and their Children (VAWC)</strong> and no recorded incidents of injury involving <strong>Children Enrolled in the Child Development Center Main</strong> of this Barangay for the period of <strong>{{ strtoupper($period) }}</strong>.
    </div>
    <div class="body-p">
        This certification is issued upon the request of <strong>{{ strtoupper($requester) }}</strong>, Child Development Worker of Barangay San Jose, Talibon, Bohol, for whatever legal purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol, Philippines.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
