<?php

namespace App\Http\Controllers;

use App\Models\TransactionType;

class InfoController extends Controller
{
    public function index()
    {
        $types = TransactionType::where('is_active', true)
            ->withCount('requirements')
            ->orderBy('name')
            ->get();

        return view('info.index', compact('types'));
    }

    public function show(TransactionType $transactionType)
    {
        abort_unless($transactionType->is_active, 404);
        $transactionType->load('requirements');

        return view('info.show', compact('transactionType'));
    }
}