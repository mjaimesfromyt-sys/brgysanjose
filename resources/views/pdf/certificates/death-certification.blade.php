@extends('pdf.layouts.letterhead')
@section('title', 'C E R T I F I C A T I O N')
@section('body')
@php
    $requester = $requestModel->requester_name ?: $resident->name;
    $namesRaw = $requestModel->deceased_name ?? '';
    $names = array_values(array_filter(array_map('trim', explode(',', $namesRaw))));
    $total = count($names);
    $half = (int) ceil($total / 2);
    $col1 = array_slice($names, 0, $half);
    $col2 = array_slice($names, $half);
@endphp
    <div class="body-p">
        THIS IS TO CERTIFY that as per records available in this office, the names listed below are bonafide residents of Barangay San Jose, Talibon, Bohol and are already deceased:
    </div>

    @if ($total > 0)
    <table style="width: 96%; margin: 6pt auto 10pt auto; font-size: 9.5pt; border-collapse: collapse;">
        @foreach ($col1 as $i => $name)
        <tr>
            <td style="width: 6%; padding: 1.5pt 3pt; vertical-align: top;">{{ $i + 1 }}.</td>
            <td style="width: 44%; padding: 1.5pt 3pt; font-weight: bold;">{{ strtoupper($name) }}</td>
            <td style="width: 6%; padding: 1.5pt 3pt; vertical-align: top;">{{ isset($col2[$i]) ? ($half + $i + 1) . '.' : '' }}</td>
            <td style="width: 44%; padding: 1.5pt 3pt; font-weight: bold;">{{ isset($col2[$i]) ? strtoupper($col2[$i]) : '' }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="body-p">
        This certification is being issued upon the request of <strong>{{ strtoupper($requester) }}</strong> for whatever legal purpose/s it may serve him/her best.
    </div>
    <div class="body-p" style="margin-bottom: 10pt;">
        Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
    </div>
@endsection
@section('signatories')
    @include('pdf.partials.sig-a')
@endsection
