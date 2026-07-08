<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermsController extends Controller
{
    /** The Terms acceptance gate shown to users who haven't accepted the current version. */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasAcceptedCurrentTerms()) {
            return redirect()->route('dashboard');
        }

        return view('legal.accept');
    }

    public function accept(Request $request): RedirectResponse
    {
        $request->validate([
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'You must agree to the Terms & Conditions to continue.',
        ]);

        $request->user()->acceptTerms();

        $this->flashSuccess('Thank you — your agreement has been recorded.');

        return redirect()->route('dashboard');
    }
}
