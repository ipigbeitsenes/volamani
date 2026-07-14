<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\PlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'plan', 'search');
        $subscriptions = $this->subscriptionService->forAdmin(20, $filters);
        $stats = $this->subscriptionService->stats();
        $plans = $this->subscriptionService->allPlans();

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'plans', 'filters'));
    }

    public function plans(): View
    {
        $plans = $this->subscriptionService->allPlans();

        return view('admin.subscriptions.plans', compact('plans'));
    }

    public function createPlan(): View
    {
        return view('admin.subscriptions.plan-form', ['plan' => new SubscriptionPlan]);
    }

    public function storePlan(PlanRequest $request): RedirectResponse
    {
        $plan = $this->subscriptionService->createPlan($request->planData());

        $this->flashSuccess("Plan \"{$plan->name}\" created.");

        return redirect()->route('admin.subscriptions.plans');
    }

    public function editPlan(SubscriptionPlan $plan): View
    {
        return view('admin.subscriptions.plan-form', compact('plan'));
    }

    public function updatePlan(PlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $this->subscriptionService->updatePlan($plan, $request->planData());

        $this->flashSuccess("Plan \"{$plan->name}\" updated.");

        return redirect()->route('admin.subscriptions.plans');
    }

    public function togglePlan(SubscriptionPlan $plan): RedirectResponse
    {
        $plan = $this->subscriptionService->togglePlan($plan);

        $this->flashSuccess("Plan \"{$plan->name}\" ".($plan->is_active ? 'activated' : 'deactivated').'.');

        return back();
    }

    public function destroyPlan(SubscriptionPlan $plan): RedirectResponse
    {
        $name = $plan->name;

        if ($this->subscriptionService->deletePlan($plan)) {
            $this->flashSuccess("Plan \"{$name}\" deleted.");
        } else {
            $this->flashError("Can't delete \"{$name}\" — vendors are subscribed to it. Deactivate it instead.");
        }

        return redirect()->route('admin.subscriptions.plans');
    }
}
