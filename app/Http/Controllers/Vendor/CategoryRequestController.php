<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\CategoryDomain;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\SubmitCategoryRequestRequest;
use App\Models\PhysicalCategory;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;
use App\Services\Taxonomy\CategoryRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryRequestController extends Controller
{
    public function __construct(private CategoryRequestService $service) {}

    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;

        return view('vendor.category-requests.index', [
            'requests'           => $this->service->forVendor($vendor),
            'digitalCategories'  => ProductCategory::active()->whereNull('parent_id')->orderBy('name')->get(),
            'physicalCategories' => PhysicalCategory::active()->roots()->orderBy('name')->get(),
            'serviceCategories'  => ServiceCategory::active()->roots()->orderBy('name')->get(),
            'domains'            => CategoryDomain::cases(),
        ]);
    }

    public function store(SubmitCategoryRequestRequest $request): RedirectResponse
    {
        $this->service->submit($request->user()->vendor, $request->validated());

        $this->flashSuccess('Your category request was submitted for review.');

        return redirect()->route('vendor.category-requests.index');
    }
}
