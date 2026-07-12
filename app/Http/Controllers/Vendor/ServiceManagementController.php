<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\CreateServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Repositories\Products\CategoryRepository;
use App\Repositories\Services\FreelanceServiceRepository;
use App\Services\Services\ServiceListingService;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function __construct(
        private FreelanceServiceRepository $serviceRepo,
        private CategoryRepository $categoryRepo,
        private ServiceListingService $serviceManager,
    ) {}

    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        $services = $this->serviceRepo->vendorServices($vendor->id);

        return view('vendor.services.index', compact('services'));
    }

    public function create()
    {
        $categories = $this->categoryRepo->allForSelect();

        return view('vendor.services.create', compact('categories'));
    }

    public function store(CreateServiceRequest $request)
    {
        $vendor = $request->user()->vendor;
        $this->serviceManager->createService($vendor, $request->validated());

        $this->flashSuccess('Service submitted for review.');

        return redirect()->route('vendor.services.index');
    }

    public function edit(Request $request, int $id)
    {
        $vendor = $request->user()->vendor;
        $service = $this->serviceRepo->findOrFail($id);

        abort_unless($service->vendor_id === $vendor->id, 403);

        $categories = $this->categoryRepo->allForSelect();

        return view('vendor.services.edit', compact('service', 'categories'));
    }

    public function update(UpdateServiceRequest $request, int $id)
    {
        $service = $this->serviceRepo->findOrFail($id);
        $this->serviceManager->updateService($service, $request->validated());

        $this->flashSuccess('Service updated successfully.');

        return redirect()->route('vendor.services.index');
    }

    public function destroy(Request $request, int $id)
    {
        $vendor = $request->user()->vendor;
        $service = $this->serviceRepo->findOrFail($id);

        abort_unless($service->vendor_id === $vendor->id, 403);

        $this->serviceManager->archiveService($service);
        $this->flashSuccess('Service archived.');

        return redirect()->route('vendor.services.index');
    }
}
