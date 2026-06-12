<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private WalletService       $walletService,
    ) {}

    public function index(): View
    {
        $vendor       = auth()->user()->vendor;
        $current      = $vendor->activeSubscription();
        $plans        = $this->subscriptionService->activePlans();
        $wallet       = $this->walletService->getOrCreate(auth()->user());
        $invoices     = $current
            ? $current->invoices()->limit(10)->get()
            : collect();

        return view('vendor.subscription.index', compact('vendor', 'current', 'plans', 'wallet', 'invoices'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $vendor = auth()->user()->vendor;
        $method = $request->input('method') === 'paystack' ? 'paystack' : 'wallet';

        $result = $this->subscriptionService->subscribe($vendor, $plan, $method);

        return match ($result['status']) {
            'redirect'     => redirect()->away($result['url']),
            'active'       => $this->done("You're now on the {$plan->name} plan."),
            'trialing'     => $this->done("Your {$plan->trial_days}-day free trial of {$plan->name} has started."),
            'exists'       => $this->info("You're already subscribed to the {$plan->name} plan."),
            'insufficient' => $this->fail('Insufficient wallet balance. Fund your wallet or pay with Paystack.'),
            default        => $this->fail('Could not start the subscription. Please try again.'),
        };
    }

    public function cancel(): RedirectResponse
    {
        $vendor  = auth()->user()->vendor;
        $current = $vendor->activeSubscription();

        if (! $current || $current->isCancelled()) {
            return $this->info('You have no active subscription to cancel.');
        }

        $this->subscriptionService->cancel($current);

        $ends = $current->ends_at?->format('d M Y');

        return $this->done("Subscription cancelled. You keep access until {$ends}.");
    }

    private function done(string $message): RedirectResponse
    {
        $this->flashSuccess($message);

        return redirect()->route('vendor.subscription.index');
    }

    private function info(string $message): RedirectResponse
    {
        // Vendor layout only surfaces success/error flashes.
        $this->flashSuccess($message);

        return redirect()->route('vendor.subscription.index');
    }

    private function fail(string $message): RedirectResponse
    {
        $this->flashError($message);

        return redirect()->route('vendor.subscription.index');
    }
}
