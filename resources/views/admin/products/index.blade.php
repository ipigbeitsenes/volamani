@extends('layouts.admin')

@section('title', 'Product Moderation')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Product Moderation</h4>

    <ul class="nav nav-pills gap-2 mb-4">
        @php
            $tabs = ['pending' => 'Pending', 'active' => 'Active', 'rejected' => 'Rejected', 'all' => 'All'];
        @endphp
        @foreach($tabs as $value => $label)
            <li class="nav-item">
                <a class="nav-link {{ $status === $value ? 'active' : '' }}" href="{{ route('admin.products.index', ['status' => $value]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Product</th><th>Vendor</th><th>Price</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->name }}</td>
                                <td class="small">{{ $product->vendor->business_name ?? '—' }}</td>
                                <td>{{ money($product->price) }}</td>
                                <td><span class="badge bg-{{ $product->status->badge() }}-subtle text-{{ $product->status->badge() }}">{{ $product->status->label() }}</span></td>
                                <td class="text-end text-nowrap">
                                    @if($product->status !== \App\Enums\ProductStatus::Active)
                                        <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-success">Approve</button>
                                        </form>
                                    @endif
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#reject{{ $product->id }}">Reject</button>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this product?');">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                    <div class="collapse mt-2 text-start" id="reject{{ $product->id }}">
                                        <form action="{{ route('admin.products.reject', $product) }}" method="POST">@csrf
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="reason" class="form-control" placeholder="Rejection reason" required>
                                                <button class="btn btn-warning">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No products in this view.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
