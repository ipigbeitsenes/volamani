<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requests\CreateProductRequestRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequestQuotation;
use App\Models\Vendor;
use App\Repositories\Products\CategoryRepository;
use App\Repositories\Requests\ProductRequestRepository;
use App\Services\Requests\ProductRequestService;
use Illuminate\Http\Request;

class ProductRequestController extends Controller
{
    public function __construct(
        private ProductRequestRepository $requestRepo,
        private CategoryRepository $categoryRepo,
        private ProductRequestService $requestService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category', 'budget_max', 'deadline', 'sort']);
        $requests = $this->requestRepo->openRequests($filters);
        $categories = $this->categoryRepo->rootCategories();

        return view('marketplace.requests.index', compact('requests', 'categories', 'filters'));
    }

    public function show(Request $request, int $id)
    {
        $productRequest = $this->requestRepo->findWithQuotations($id);
        abort_if(! $productRequest, 404);

        // Private (direct) requests: only the buyer and the targeted vendor may view.
        if (! $productRequest->is_public
            && $productRequest->buyer_id !== auth()->id()
            && $productRequest->vendor_id !== auth()->user()?->vendor?->id) {
            abort(403);
        }

        $user = $request->user();
        $isBuyer = $user && $productRequest->buyer_id === $user->id;
        $vendorRecord = $user?->vendor;
        $hasQuoted = $vendorRecord && $productRequest->hasQuotedBy($vendorRecord);
        $myQuotation = $hasQuoted ? $productRequest->getQuotationBy($vendorRecord) : null;

        if ($myQuotation) {
            $myQuotation->markViewed();
        }

        return view('marketplace.requests.show', compact(
            'productRequest', 'isBuyer', 'vendorRecord', 'hasQuoted', 'myQuotation'
        ));
    }

    public function create(Request $request)
    {
        $categories = $this->categoryRepo->allForSelect();
        $targetVendor = $request->filled('vendor')
            ? Vendor::with('user')->find($request->integer('vendor'))
            : null;

        return view('marketplace.requests.create', compact('categories', 'targetVendor'));
    }

    public function store(CreateProductRequestRequest $request)
    {
        $productRequest = $this->requestService->createRequest(
            $request->user(),
            $request->validated()
        );

        $this->flashSuccess($productRequest->isDirect()
            ? 'Request sent directly to the seller. You\'ll be notified when they respond.'
            : 'Request posted! Vendors will start submitting quotations shortly.');

        return redirect()->route('marketplace.requests.show', $productRequest->id);
    }

    public function myRequests(Request $request)
    {
        $requests = $this->requestRepo->buyerRequests($request->user()->id);

        return view('marketplace.requests.my', compact('requests'));
    }

    public function acceptQuotation(Request $request, int $requestId, int $quotationId)
    {
        $productRequest = ProductRequest::findOrFail($requestId);
        abort_unless($productRequest->buyer_id === $request->user()->id, 403);

        $quotation = ProductRequestQuotation::findOrFail($quotationId);

        $this->requestService->acceptQuotation($productRequest, $quotation);

        $this->flashSuccess('Quotation accepted! The vendor has been notified.');

        return redirect()->route('marketplace.requests.show', $productRequest->id);
    }

    public function close(Request $request, int $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        abort_unless($productRequest->buyer_id === $request->user()->id, 403);

        $this->requestService->closeRequest($productRequest);

        $this->flashSuccess('Request closed.');

        return redirect()->route('marketplace.requests.my');
    }
}
