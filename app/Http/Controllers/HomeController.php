<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        try {
            $stats = [
                'vendors' => User::role('vendor')->count(),
                'buyers' => User::role('buyer')->count(),
            ];
        } catch (\Exception $e) {
            $stats = ['vendors' => 0, 'buyers' => 0];
        }

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('home', compact('stats', 'plans'));
    }
}
