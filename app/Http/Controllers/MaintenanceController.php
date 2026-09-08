<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class MaintenanceController extends Controller
{
    public function index()
    {
        $message = Setting::get('maintenance_message', 'Kami sedang melakukan pemeliharaan untuk meningkatkan kualitas layanan. Silakan coba lagi nanti.');

        return view('maintenance', compact('message'));
    }
}
