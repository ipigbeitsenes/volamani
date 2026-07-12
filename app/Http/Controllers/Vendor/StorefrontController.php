<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Repositories\Vendors\VendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StorefrontController extends Controller
{
    public function __construct(private VendorRepository $vendorRepo) {}

    /** Public directory of stores buyers can browse and follow. */
    public function index(Request $request): View
    {
        $vendors = $this->vendorRepo->directory(
            $request->only('q', 'category', 'sort')
        );

        return view('marketplace.vendors.index', compact('vendors'));
    }

    public function show(Request $request, string $username): View
    {
        $vendor = $this->vendorRepo->findByUsername($username);

        if (! $vendor || ! $vendor->isActive()) {
            throw new NotFoundHttpException('Storefront not found.');
        }

        $vendor->incrementViews();

        $products = Schema::hasTable('products')
            ? $vendor->products()->where('status', 'active')->latest()->limit(12)->get()
            : collect();

        $services = Schema::hasTable('freelance_services')
            ? $vendor->services()->where('status', 'active')->latest()->limit(6)->get()
            : collect();

        $recentReviews = Schema::hasTable('reviews')
            ? $vendor->reviews()->with('reviewer')->latest()->limit(5)->get()
            : collect();

        return view('marketplace.storefront.show', compact('vendor', 'products', 'services', 'recentReviews'));
    }
}
