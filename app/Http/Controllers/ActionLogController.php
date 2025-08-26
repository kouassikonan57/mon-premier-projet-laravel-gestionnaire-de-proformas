<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;

class ActionLogController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $logs = ActionLog::with(['user', 'proforma', 'facture'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            $logs = ActionLog::with(['user', 'proforma', 'facture'])
                ->where('filiale_id', $user->filiale_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('action_logs.index', compact('logs'));
    }
}