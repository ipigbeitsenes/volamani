<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Vendors\AddStrikeAction;
use App\Actions\Vendors\ClearStrikeAction;
use App\Enums\StrikeReason;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorStrike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class VendorStrikeController extends Controller
{
    public function store(Request $request, Vendor $vendor, AddStrikeAction $addStrike): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', new Enum(StrikeReason::class)],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        $addStrike->execute(
            $vendor,
            StrikeReason::from($data['reason']),
            $data['note'] ?? null,
            null,
            $request->user(),
        );

        $this->flashWarning("Strike recorded against {$vendor->business_name}.");

        return back();
    }

    public function clear(Request $request, VendorStrike $strike, ClearStrikeAction $clearStrike): RedirectResponse
    {
        $clearStrike->execute($strike, $request->user());

        $this->flashSuccess('Strike cleared.');

        return back();
    }
}
