<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'BARANGAY BUSINESS CLEARANCE',
                'slug' => NULL,
                'description' => NULL,
                'requires_residency' => false,
                'fee' => 330.00,
                'is_active' => true,
                'requirements' => [
                    'Valid ID',
                ],
            ],
            [
                'name' => 'BARANGAY AGREEMENT',
                'slug' => NULL,
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 300.00,
                'is_active' => true,
                'requirements' => [
                    'Valid ID',
                ],
            ],
            [
                'name' => 'MOTOR LOAN STATE THE INCOME',
                'slug' => NULL,
                'description' => NULL,
                'requires_residency' => false,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATE OF INDIGENCY',
                'slug' => 'indigency',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION OF GOOD MORAL',
                'slug' => 'good-moral',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CERTIFICATE OF RESIDENCY',
                'slug' => 'residency-regular',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION OF STATE OF CALAMITY',
                'slug' => 'state-of-calamity',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'DSWD FINANCIAL ASSISTANCE CERTIFICATION',
                'slug' => 'dswd-burdened',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY RESIDENCY WITH PURPOSE',
                'slug' => 'residency-with-purpose',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'SOLO PARENT INDIGENT',
                'slug' => 'solo-parent-indigent',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'TREE CUTTING PERMIT',
                'slug' => 'tree-cutting-permit',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CLEARANCE FOR ELECTRICAL CONNECTION',
                'slug' => 'electrical-connection',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CLEARANCE',
                'slug' => 'barangay-clearance',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATE OF GOOD MORAL (PNP/NBI)',
                'slug' => 'good-moral-unlawful',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'SOLO PARENT CERTIFICATION',
                'slug' => 'solo-parent',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATE OF LIFE (STILL ALIVE)',
                'slug' => 'still-alive',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - LAND NO ADVERSE CLAIMS',
                'slug' => 'land-no-claims',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - AGRICULTURAL TENANT',
                'slug' => 'land-tenant',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - SUBDIVISION SURVEY DENR',
                'slug' => 'land-subdivision-denr',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - LAND RECLASSIFICATION',
                'slug' => 'land-reclassification',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'PWD CERTIFICATION (REGISTRATION)',
                'slug' => 'pwd-registration',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'PWD CERTIFICATION (DECEASED)',
                'slug' => 'pwd-deceased',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'SENIOR CITIZEN CERTIFICATION (DECEASED)',
                'slug' => 'senior-deceased',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'SENIOR CITIZEN CERTIFICATION (BEDRIDDEN)',
                'slug' => 'senior-bedridden',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY RESIDENCY (DECEASED)',
                'slug' => 'residency-deceased',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY RESIDENCY (CAREGIVING)',
                'slug' => 'residency-caregiving',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY RESIDENCY (TRANSFER IN)',
                'slug' => 'residency-transfer-in',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - FAMILY TRANSFER OUT',
                'slug' => 'family-transfer-out',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CERTIFICATION - COMMON LAW PARTNER',
                'slug' => 'common-law-partner',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION OF EMPLOYMENT',
                'slug' => 'employment-cert',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CLEARANCE (FENCING PERMIT)',
                'slug' => 'fencing-permit',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATE OF INDIGENCY (SCHOLARSHIP)',
                'slug' => 'indigency-scholarship',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CERTIFICATE (LATE REGISTRATION)',
                'slug' => 'residency-late-registration',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 0.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - TREE PLANTING',
                'slug' => 'tree-planting',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'DSWD CERTIFICATION (BELOW MINIMUM WAGE)',
                'slug' => 'dswd-below-wage',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'SOLO PARENT CERTIFICATION (VARIANT)',
                'slug' => 'solo-parent-cert',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CERTIFICATION - VAWC CLEARANCE',
                'slug' => 'vawc-no-cases',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 0.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CERTIFICATION - PEST CONTROL',
                'slug' => 'pest-control',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATE OF ATTESTATION',
                'slug' => 'certificate-attestation',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY CLEARANCE - TESDA EMPLOYMENT',
                'slug' => 'employment-tesda',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'BARANGAY RESIDENCY (DECEASED PARENT)',
                'slug' => 'residency-deceased-parent',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION - LAND HEIRS SUBDIVISION',
                'slug' => 'land-heirs-subdivision',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
            [
                'name' => 'CERTIFICATION OF DEATH (MULTIPLE)',
                'slug' => 'death-certification',
                'description' => NULL,
                'requires_residency' => true,
                'fee' => 180.00,
                'is_active' => true,
                'requirements' => [
                ],
            ],
        ];

        foreach ($types as $t) {
            $requirements = $t['requirements'];
            unset($t['requirements']);
            $type = TransactionType::firstOrCreate(['name' => $t['name']], $t);
            if ($type->requirements()->count() === 0) {
                foreach ($requirements as $item) {
                    $type->requirements()->create(['item' => $item]);
                }
            }
        }
    }
}
