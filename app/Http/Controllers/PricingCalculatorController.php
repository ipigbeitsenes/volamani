<?php

namespace App\Http\Controllers;

use App\Enums\PricingCategory;
use App\Enums\PricingType;
use App\Http\Requests\Pricing\CalculatePriceRequest;
use App\Repositories\Pricing\PricingRepository;
use App\Services\Pricing\PricingCalculatorService;
use Illuminate\Http\Request;

class PricingCalculatorController extends Controller
{
    public function __construct(
        private PricingCalculatorService $calculator,
        private PricingRepository $repository,
    ) {}

    public function index(Request $request)
    {
        $categories = PricingCategory::options();
        $pricingTypes = PricingType::cases();

        $selectedCategory = $request->query('category');
        $templates = $selectedCategory ? $this->repository->templatesByCategory($selectedCategory) : collect();
        $addOns = $selectedCategory ? $this->repository->addOnsByCategory($selectedCategory) : collect();

        $myEstimates = null;
        if (auth()->check()) {
            $myEstimates = $this->repository->userEstimates(auth()->id());
        }

        return view('marketplace.pricing-calculator.index', compact(
            'categories', 'pricingTypes', 'selectedCategory', 'templates', 'addOns', 'myEstimates'
        ));
    }

    public function calculate(CalculatePriceRequest $request)
    {
        $data = $request->validated();
        $breakdown = $this->calculator->calculate($data);

        if ($request->boolean('save')) {
            $user = auth()->user();
            $token = $request->session()->getId();
            $estimate = $this->calculator->saveEstimate($data, $breakdown, $user, $token);

            return redirect()->route('pricing-calculator.show', $estimate->reference)
                ->with('success', 'Estimate saved!');
        }

        // Return JSON for live preview (AJAX requests)
        if ($request->expectsJson()) {
            return response()->json([
                'breakdown' => $breakdown,
                'formatted' => [
                    'base_price' => money($breakdown['base_price']),
                    'add_ons_total' => money($breakdown['add_ons_total']),
                    'subtotal' => money($breakdown['subtotal']),
                    'urgency_surcharge' => money($breakdown['urgency_surcharge']),
                    'total' => money($breakdown['total']),
                ],
            ]);
        }

        // Flash and show result inline
        return view('marketplace.pricing-calculator.result', [
            'breakdown' => $breakdown,
            'data' => $data,
            'categories' => PricingCategory::options(),
            'pricingTypes' => PricingType::cases(),
        ]);
    }

    public function save(CalculatePriceRequest $request)
    {
        $data = $request->validated();
        $breakdown = $this->calculator->calculate($data);
        $user = auth()->user();
        $token = $request->session()->getId();
        $estimate = $this->calculator->saveEstimate($data, $breakdown, $user, $token);

        return redirect()->route('pricing-calculator.show', $estimate->reference)
            ->with('success', 'Estimate saved successfully!');
    }

    public function show(string $reference)
    {
        $estimate = $this->repository->findEstimate($reference);
        abort_if(! $estimate, 404);

        // Only owner or guest who created it can view
        if ($estimate->user_id && auth()->id() !== $estimate->user_id) {
            abort(403);
        }

        return view('marketplace.pricing-calculator.result', compact('estimate'));
    }

    public function myEstimates()
    {
        $estimates = $this->repository->userEstimates(auth()->id());

        return view('marketplace.pricing-calculator.my-estimates', compact('estimates'));
    }

    public function loadTemplates(Request $request)
    {
        $category = $request->query('category');

        if (! $category) {
            return response()->json(['templates' => [], 'add_ons' => []]);
        }

        $templates = $this->repository->templatesByCategory($category);
        $addOns = $this->repository->addOnsByCategory($category);

        return response()->json([
            'templates' => $templates->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'pricing_type' => $t->pricing_type->value,
                'base_price' => from_kobo($t->base_price),
                'hourly_rate' => from_kobo($t->hourly_rate),
                'min_hours' => $t->min_hours,
                'max_hours' => $t->max_hours,
                'description' => $t->description,
                'features' => $t->features,
                'price_range' => $t->priceRange(),
            ]),
            'add_ons' => $addOns->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'description' => $a->description,
                'price' => $a->is_percentage ? ($a->price / 100) : from_kobo($a->price),
                'is_percentage' => $a->is_percentage,
                'display_price' => $a->displayPrice(),
            ]),
        ]);
    }
}
