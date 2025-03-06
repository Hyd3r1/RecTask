<?php

namespace App\Http\Controllers;

use App\Http\Integrations\OpenMeteo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard');
    }
}
