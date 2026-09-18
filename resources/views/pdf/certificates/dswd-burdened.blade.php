@extends('pdf.layouts.letterhead')
@section('title', 'CERTIFICATION')
@section('body')
    <div class="body-p">
        This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>, of legal age, is a bonafide resident of Barangay San Jose, Talibon, Bohol.
    </div>
    <div class="body-p">
        Further, it is hereby certified that his/her family is currently burdened by the effects of high inflation and the rising cost of living in the province. Due to these economic challenges, his/her income is insufficient to meet the daily basic needs of the household, thereby placing the family in a crisis situation.
    </div>
    <div class="body-p">
        This certification is issued in support of Mr./Ms. <strong>{{ strtoupper($resident->name) }}</strong> application for financial assistance from the Department of Social Welfare and Development (DSWD), to help address his/her current crisis situation.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
