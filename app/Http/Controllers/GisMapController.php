<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GisMapController extends Controller
{
    public function index()
    {
        // 1. Live resident counts kada Purok gikan sa users table
        $purokCounts = [
            'Purok 1' => User::where('purok', 'like', '%1%')->count(),
            'Purok 2' => User::where('purok', 'like', '%2%')->count(),
            'Purok 3' => User::where('purok', 'like', '%3%')->count(),
            'Purok 4' => User::where('purok', 'like', '%4%')->count(),
            'Purok 5' => User::where('purok', 'like', '%5%')->count(),
            'Purok 6' => User::where('purok', 'like', '%6%')->count(),
            'Purok 7' => User::where('purok', 'like', '%7%')->count(),
        ];

        $totalResidents = User::where('role', 'resident')->count();

        return view('map.index', compact('purokCounts', 'totalResidents'));
    }
}