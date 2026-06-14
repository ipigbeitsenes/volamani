<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryDomain;
use App\Enums\CategoryRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\CategoryRequest;
use App\Services\Taxonomy\CategoryRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryRequestController extends Controller
{
    public function __construct(private CategoryRequestService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'domain', 'search');

        return view('admin.category-requests.index', [
            'requests'  => $this->service->allForAdmin($filters),
            'filters'   => $filters,
            'statuses'  => CategoryRequestStatus::cases(),
            'domains'   => CategoryDomain::cases(),
        ]);
    }

    public function approve(Request $request, CategoryRequest $categoryRequest): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $this->service->approve($categoryRequest, $request->user(), $data['admin_note'] ?? null);
        $this->flashSuccess("Category \"{$categoryRequest->name}\" approved and added to the {$categoryRequest->domain->label()} taxonomy.");

        return back();
    }

    public function reject(Request $request, CategoryRequest $categoryRequest): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'max:500']]);

        $this->service->reject($categoryRequest, $request->user(), $data['admin_note']);
        $this->flashWarning("Category request \"{$categoryRequest->name}\" was rejected.");

        return back();
    }
}
