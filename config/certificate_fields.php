<?php

/**
 * Mapping sa certificate slug ngadto sa mga field nga kinahanglan.
 * Ang form mo-render base niini.
 */
return [

    // ── Subject nga lahi sa nag-request ──
    'subject' => [
        'senior-bedridden', 'senior-deceased', 'pwd-deceased', 'pwd-registration',
        'residency-deceased', 'residency-deceased-parent', 'death-certification',
        'common-law-partner', 'land-heirs-subdivision',
    ],

    // ── Requester (kung dili resident ang subject) ──
    'requester' => [
        'senior-bedridden', 'senior-deceased', 'pwd-deceased', 'pwd-registration',
        'residency-deceased', 'residency-deceased-parent', 'death-certification',
        'residency-caregiving', 'vawc-no-cases', 'pest-control',
        'land-no-claims', 'land-tenant', 'land-subdivision-denr',
        'land-reclassification', 'land-heirs-subdivision',
    ],

    // ── Land / property ──
    'land' => [
        'land-no-claims', 'land-tenant', 'land-subdivision-denr',
        'land-reclassification', 'land-heirs-subdivision',
    ],

    // ── Deceased details ──
    'deceased' => [
        'pwd-deceased', 'senior-deceased', 'residency-deceased-parent',
    ],

    // ── Control number (PWD / Senior) ──
    'control_no' => [
        'pwd-registration', 'pwd-deceased', 'senior-bedridden', 'senior-deceased',
    ],

    // ── Disability type ──
    'disability' => [
        'pwd-registration', 'pwd-deceased',
    ],

    // ── Solo parent since / years together ──
    'since_year' => [
        'solo-parent', 'solo-parent-cert', 'common-law-partner',
    ],

    // ── Occupation & income ──
    'income' => [
        'solo-parent-indigent', 'indigency-scholarship', 'dswd-below-wage',
        'certificate-attestation', 'employment-cert', 'employment-tesda',
    ],

    // ── Parents' names ──
    'parents' => [
        'indigency-scholarship', 'residency-late-registration',
    ],

    // ── Spouse ──
    'spouse' => [
        'fencing-permit', 'land-tenant', 'land-reclassification',
    ],

    // ── Employer ──
    'employer' => [
        'employment-cert', 'tree-planting',
    ],

    // ── Activity / service date ──
    'activity_date' => [
        'tree-planting', 'pest-control',
    ],

    // ── Multiple names (comma-separated) ──
    'name_list' => [
        'death-certification', 'land-heirs-subdivision',
    ],
];
