<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requests\SubmitQuotationRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequestQuotation;
use App\Services\Requests\ProductRequestService;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function __construct(private ProductRequestService $requestService) {}

    public function index(Request $request)
    {
        $vendor     = $request->user()->vendor;
        $quotations = ProductRequestQuotation::with(['request.category', 'request.buyer'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(15);

        return view('vendor.quotations.index', compact('quotations'));
    }

    public function store(SubmitQuotationRequest $request, int $productRequestId)
    {
        $productRequest = ProductRequest::findOrFail($productRequestId);
        $vendor         = $request->user()->vendor;

        $this->requestService->submitQuotation($productRequest, $vendor, $request->validated());

        $this->flashSuccess('Quotation submitted successfully.');
        return redirect()->route('marketplace.requests.show', $productRequest->id);
    }

    public function show(Request $request, int $quotationId)
    {
        $vendor    = $request->user()->vendor;
        $quotation = ProductRequestQuotation::with(['request.category', 'request.buyer'])
            ->where('vendor_id', $vendor->id)
            ->findOrFail($quotationId);

        $quotation->markViewed();

        return view('vendor.quotations.show', compact('quotation'));
    }

    public function destroy(Request $request, int $quotationId)
    {
        $vendor    = $request->user()->vendor;
        $quotation = ProductRequestQuotation::where('vendor_id', $vendor->id)->findOrFail($quotationId);

        $this->requestService->withdrawQuotation($quotation, $vendor);

        $this->flashSuccess('Quotation withdrawn.');
        return redirect()->route('vendor.quotations.index');
    }
}
