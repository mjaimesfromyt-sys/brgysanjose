<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\ActivityLog;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class DocumentVerificationController extends Controller
{
    public function verify($code)
    {
        // Pangitaon ang dokumento gamit ang Cryptographic Token nga anaa sa QR
        $doc = DocumentRequest::where('verification_code', $code)->first();

        if (! $doc) {
            if (view()->exists('verification.invalid')) {
                return response()->view('verification.invalid', ['code' => $code], 404);
            }
            abort(404, 'Invalid or Unrecognized Document Verification Token.');
        }
        // IAPIL DINHI
                    ActivityLog::create([
                 'user_name' => 'Public Verification',
                     'role' => 'Public',
                        'action' => 'Document QR Verified',
                         'control_number' => $doc->control_number,
                        'ip_address' => request()->ip(),
]);

        // Defensive lookup sa resident ug document type
        $resident = $doc->user ?? \App\Models\User::find($doc->user_id);
        
        $typeName = 'Barangay Certification';
        if (method_exists($doc, 'transactionType') && $doc->transactionType) {
            $typeName = $doc->transactionType->name;
        } elseif ($doc->transaction_type_id) {
            $tt = TransactionType::find($doc->transaction_type_id);
            if ($tt) $typeName = $tt->name;
        }

        return view('verification.document', [
            'doc'               => $doc,
            'document'          => $doc,
            'docTypeName'       => $typeName, // 👉 Gi-provide para sa Line 92
            'documentType'      => $typeName,
            'resident'          => $resident,
            'user'              => $resident,
            'controlNumber'     => $doc->control_number,
            'control_number'    => $doc->control_number,
            'issuedAt'          => $doc->issued_at ?? $doc->created_at,
            'issued_at'         => $doc->issued_at ?? $doc->created_at,
            'purpose'           => $doc->purpose,
            'status'            => $doc->status,
            'code'              => $code,
            'verificationCode'  => $code,
            'verification_code' => $code,
        ]);
    }
}