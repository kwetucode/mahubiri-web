<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index()
    {
        return Inertia::render('Admin/Settings');
    }
}
