<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class HistoriqueController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $logs = ActivityLog::latest()->paginate(20);
        } else {
            $logs = ActivityLog::where('filiale_id', $user->filiale_id)
                ->latest()
                ->paginate(20);
        }

        return view('historique.index', compact('logs'));
    }
}