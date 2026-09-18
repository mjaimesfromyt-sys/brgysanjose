@php
    $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok')
        ? $resident->purok
        : 'Purok ' . ($resident->purok ?? '1');
    $ageDisplay    = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
    $statusDisplay = !empty($civilStatus) ? ucfirst($civilStatus) : 'Single';
    $stayText      = !empty($lengthOfStay)
        ? ' (residing for ' . $lengthOfStay . ' ' . ($lengthOfStay == 1 ? 'year' : 'years') . ')'
        : '';
    $residentName  = strtoupper($resident->name ?? '');
    $issuedDay     = now()->format('jS');
    $issuedMonthYear = now()->format('F, Y');
@endphp
