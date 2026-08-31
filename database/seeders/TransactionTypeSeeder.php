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
                'name' => 'Barangay Clearance',
                'description' => 'General clearance certifying good standing in the barangay.',
                'requires_residency' => true,
                'fee' => 50.00,
                'requirements' => [
                    'Barangay Clearance Application Form',
                    'Photocopy of Valid Government ID',
                    'Proof of Residency (e.g. utility bill)',
                    'Two (2) Valid 2x2 ID Pictures',
                    'Community Tax Certificate (Cedula)',
                ],
            ],
            [
                'name' => 'Certificate of Indigency',
                'description' => 'Certifies that the resident belongs to an indigent family.',
                'requires_residency' => true,
                'fee' => 20.00,
                'requirements' => [
                    'Valid Government ID',
                    'Proof of Residency',
                    'Statement of purpose',
                ],
            ],
            [
                'name' => 'Barangay Business Permit',
                'description' => 'Permit to operate a business within the barangay.',
                'requires_residency' => false,
                'fee' => 200.00,
                'requirements' => [
                    'Business Permit Application Form',
                    'Valid Government ID of owner',
                    'DTI/SEC Registration',
                    'Proof of business address within the barangay',
                    'Community Tax Certificate (Cedula)',
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