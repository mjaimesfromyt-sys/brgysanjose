@extends('pdf.layouts.letterhead')
@section('title', 'BARANGAY CERTIFICATION')
@section('body')
@php
    $requester = $requestModel->requester_name ?: $resident->name;
    $serviceDate = $requestModel->date_died ? \Carbon\Carbon::parse($requestModel->date_died)->format('F d, Y') : '__________';
@endphp
    <div class="body-p">
        This is to certify that the <strong>CHILD DEVELOPMENT CENTER OF BARANGAY SAN JOSE MAIN</strong> with <strong>{{ strtoupper($requester) }}</strong> as the <strong>CHILD DEVELOPMENT WORKER</strong> has undergone pest control service last <strong>{{ $serviceDate }}</strong> to ensure cleaner and healthier environment before the classes start.
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
