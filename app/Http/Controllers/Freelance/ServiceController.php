<?php

namespace App\Http\Controllers\Freelance;

use App\Http\Controllers\Controller;
use App\Repositories\Products\CategoryRepository;
use App\Repositories\Services\FreelanceServiceRepository;
use App\Services\Services\ServiceListingService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private FreelanceServiceRepository $serviceRepo,
        private CategoryRepository $categoryRepo,
        private ServiceListingService $listingService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category', 'delivery', 'budget', 'vendor', 'sort']);
        $services = $this->serviceRepo->searchServices($filters);
        $categories = $this->categoryRepo->rootCategories();
        $featured = $this->serviceRepo->featuredServices(4);

        return view('marketplace.services.index', compact('services', 'categories', 'featured', 'filters'));
    }

    public function show(string $slug)
    {
        $service = $this->serviceRepo->findBySlug($slug);

        if (! $service || ! $service->isActive()) {
            abort(404);
        }

        $service->incrementViews();
        $related = $this->serviceRepo->relatedServices($service);
        $reviews = $service->reviews()->with('reviewer')->latest()->take(10)->get();
        $hasPurchased = auth()->check()
            && $service->orders()
                ->where('buyer_id', auth()->id())
                ->where('status', 'completed')
                ->exists();

        return view('marketplace.services.show', compact('service', 'related', 'reviews', 'hasPurchased'));
    }

    public function placeOrder(Request $request, string $slug)
    {
        $service = $this->serviceRepo->findBySlug($slug);

        if (! $service || ! $service->isActive()) {
            abort(404);
        }

        abort_if(
            $service->vendor?->user_id === auth()->id(),
            403,
            'You cannot order your own service.'
        );

        $package = $service->packages()->findOrFail($request->integer('package_id'));

        $order = $this->listingService->placeOrder($service, $package, auth()->user());

        return redirect()->route('checkout.service-order', $order)
            ->with('success', 'Order created. Complete payment to start your project.');
    }
}
