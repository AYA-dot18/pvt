<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use Inertia\Inertia;
 use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function index()
    {
        $logs = Activity::with('causer')->latest()->paginate(100);

        return Inertia::render('Logs/Index',compact('logs'));
    }
    public function index_2()
    {

        
    $logs = Activity::with('causer')->latest()->paginate(100);
    
    return Inertia::render('Logs/Index', ['logs' => $logs]);

    
    }


}
