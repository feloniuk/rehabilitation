<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Show the platform landing page.
     */
    public function index()
    {
        $tenantsCount = Tenant::where('is_active', true)->count();

        return view('platform.landing', compact('tenantsCount'));
    }

    /**
     * Show pricing page.
     */
    public function pricing()
    {
        return view('platform.pricing');
    }

    /**
     * Show features page.
     */
    public function features()
    {
        return view('platform.features');
    }
}
