<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductModerationController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', ProductStatus::Pending->value);

        $query = Product::with('vendor')->latest();
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        $products = $query->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products', 'status'));
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->admin->approveProduct($product, auth()->user());
        $this->flashSuccess("\"{$product->name}\" approved.");

        return back();
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->admin->rejectProduct($product, auth()->user(), $data['reason']);
        $this->flashWarning("\"{$product->name}\" rejected.");

        return back();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->admin->deleteProduct($product);
        $this->flashSuccess('Product removed.');

        return back();
    }
}
