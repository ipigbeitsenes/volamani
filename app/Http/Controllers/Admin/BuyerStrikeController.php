<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Buyers\AddBuyerStrikeAction;
use App\Actions\Buyers\ClearBuyerStrikeAction;
use App\Enums\BuyerStrikeReason;
use App\Http\Controllers\Controller;
use App\Models\BuyerStrike;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class BuyerStrikeController extends Controller
{
    /** Buyers with any abuse strikes — flagged and suspended surfaced first. */
    public function index(Request $request): View
    {
        $filter = $request->query('filter'); // flagged | suspended | null

        $buyers = User::query()
            ->where('buyer_strikes', '>', 0)
            ->when($filter === 'flagged', fn ($q) => $q->where('buyer_flagged', true))
            ->when($filter === 'suspended', fn ($q) => $q->where('purchases_suspended', true))
            ->withCount(['buyerStrikes as active_strikes_count' => fn ($q) => $q->active()])
            ->orderByDesc('purchases_suspended')
            ->orderByDesc('buyer_flagged')
            ->orderByDesc('buyer_strikes_updated_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'flagged' => User::where('buyer_flagged', true)->count(),
            'suspended' => User::where('purchases_suspended', true)->count(),
        ];

        return view('admin.buyer-strikes.index', compact('buyers', 'stats', 'filter'));
    }

    public function show(User $user): View
    {
        $user->load(['buyerStrikes' => fn ($q) => $q->latest(), 'buyerStrikes.issuedBy', 'buyerStrikes.clearedBy']);

        return view('admin.buyer-strikes.show', compact('user'));
    }

    public function store(Request $request, User $user, AddBuyerStrikeAction $addStrike): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', new Enum(BuyerStrikeReason::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $addStrike->execute(
            $user,
            BuyerStrikeReason::from($data['reason']),
            $data['note'] ?? null,
            null,
            $request->user(),
        );

        $this->flashWarning("Strike recorded against {$user->name}.");

        return back();
    }

    public function clear(Request $request, BuyerStrike $strike, ClearBuyerStrikeAction $clearStrike): RedirectResponse
    {
        $clearStrike->execute($strike, $request->user());

        $this->flashSuccess('Strike cleared.');

        return back();
    }

    /** Lift all restrictions by clearing every remaining active strike. */
    public function reinstate(Request $request, User $user, ClearBuyerStrikeAction $clearStrike): RedirectResponse
    {
        $active = $user->buyerStrikes()->active()->get();

        foreach ($active as $strike) {
            $clearStrike->execute($strike, $request->user());
        }

        $this->flashSuccess("{$user->name} reinstated — {$active->count()} strike(s) cleared.");

        return back();
    }
}
