@extends('layouts.app')

@section('title', isset($estimate) ? 'Estimate ' . $estimate->reference : 'Pricing Estimate')

@section('content')
<div class="container py-4" style="max-width:800px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">
                @isset($estimate)
                    Estimate #{{ $estimate->reference }}
                @else
                    Your Pricing Estimate
                @endisset
            </h4>
            @isset($estimate)
                <small class="text-muted">{{ $estimate->service_name }}</small>
            @endisset
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pricing-calculator.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> New Estimate
            </a>
            @isset($estimate)
                <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Print / Save PDF
                </button>
            @endisset
        </div>
    </div>

    @php
        $bd = $estimate ?? null;
        $breakdown = $bd ? [
            'pricing_type'       => $bd->pricing_type->value,
            'urgency'            => $bd->urgency,
            'urgency_multiplier' => (float) $bd->urgency_multiplier,
            'base_price'         => $bd->base_price,
            'hourly_rate'        => $bd->hourly_rate,
            'estimated_hours'    => (float) $bd->estimated_hours,
            'add_ons'            => $bd->add_ons ?? [],
            'add_ons_total'      => $bd->add_ons_total,
            'milestones'         => $bd->milestones ?? [],
            'subtotal'           => $bd->subtotal,
            'total'              => $bd->total,
            'urgency_surcharge'  => $bd->total - $bd->subtotal,
        ] : ($breakdown ?? []);
    @endphp

    {{-- Client info (quotation header) --}}
    @if (!empty($estimate?->client_name) || !empty($estimate?->client_email))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-1">Quotation Prepared For</h5>
                        @if ($estimate->client_name)
                            <p class="mb-0 fw-semibold">{{ $estimate->client_name }}</p>
                        @endif
                        @if ($estimate->client_email)
                            <p class="mb-0 text-muted">{{ $estimate->client_email }}</p>
                        @endif
                    </div>
                    <div class="text-end text-muted small">
                        @isset($estimate)
                            <div>Ref: <strong>{{ $estimate->reference }}</strong></div>
                            <div>{{ $estimate->created_at->format('d M Y') }}</div>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Category & Service --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">
                @isset($estimate)
                    {{ $estimate->service_name }}
                @else
                    {{ $data['service_name'] ?? 'Estimate' }}
                @endisset
            </h5>
            <div class="d-flex flex-wrap gap-2 mb-0">
                <span class="badge bg-primary bg-opacity-10 text-primary border">
                    @isset($estimate)
                        {{ $estimate->category->label() }}
                    @else
                        {{ \App\Enums\PricingCategory::from($data['category'] ?? 'website')->label() }}
                    @endisset
                </span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                    {{ \App\Enums\PricingType::from($breakdown['pricing_type'])->label() }}
                </span>
                <span class="badge bg-{{ $estimate?->urgencyBadge() ?? 'secondary' }} bg-opacity-10 text-dark border">
                    {{ $estimate?->urgencyLabel() ?? ucfirst($breakdown['urgency'] ?? 'normal') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Milestones --}}
    @if (!empty($breakdown['milestones']))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Project Milestones</h5>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Milestone</th><th>Description</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($breakdown['milestones'] as $i => $m)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $m['name'] ?? '' }}</td>
                                <td class="text-muted small">{{ $m['description'] ?? '' }}</td>
                                <td class="text-end fw-semibold">{{ money(to_kobo((float) ($m['amount'] ?? 0))) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pricing breakdown --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Price Breakdown</h5>
            <table class="table mb-0">
                <tbody>
                    <tr>
                        <td>
                            @if ($breakdown['pricing_type'] === 'hourly')
                                Base Price ({{ $breakdown['estimated_hours'] }} hrs × {{ money($breakdown['hourly_rate']) }}/hr)
                            @elseif ($breakdown['pricing_type'] === 'milestone')
                                Milestone Total
                            @else
                                Base Price
                            @endif
                        </td>
                        <td class="text-end">{{ money($breakdown['base_price']) }}</td>
                    </tr>

                    @if (!empty($breakdown['add_ons']))
                        @foreach ($breakdown['add_ons'] as $addOn)
                            <tr>
                                <td class="text-muted">+ {{ $addOn['name'] }}</td>
                                <td class="text-end text-muted">{{ money($addOn['price']) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-top">
                            <td class="fw-semibold">Add-ons Subtotal</td>
                            <td class="text-end fw-semibold">{{ money($breakdown['add_ons_total']) }}</td>
                        </tr>
                    @endif

                    <tr class="border-top">
                        <td class="fw-semibold">Subtotal</td>
                        <td class="text-end fw-semibold">{{ money($breakdown['subtotal']) }}</td>
                    </tr>

                    @if (($breakdown['urgency_multiplier'] ?? 1.0) > 1.0)
                        <tr class="text-danger">
                            <td>Urgency Surcharge (×{{ $breakdown['urgency_multiplier'] }})</td>
                            <td class="text-end">+ {{ money($breakdown['urgency_surcharge']) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <th class="fs-5">Total Estimate</th>
                        <th class="text-end fs-5 text-success">{{ money($breakdown['total']) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Notes --}}
    @if (!empty($estimate?->notes) || !empty($data['notes']))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Notes</h5>
                <p class="mb-0">{{ $estimate?->notes ?? $data['notes'] }}</p>
            </div>
        </div>
    @endif

    {{-- Save CTA (for non-saved view) --}}
    @if (!isset($estimate))
        <div class="card border-0 shadow-sm mb-4 bg-primary-subtle">
            <div class="card-body text-center">
                <p class="mb-3">Save this estimate to reference later or share with your client.</p>
                <form method="POST" action="{{ route('pricing-calculator.save') }}">
                    @csrf
                    @foreach ($data ?? [] as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $i => $item)
                                @if (is_array($item))
                                    @foreach ($item as $subKey => $subVal)
                                        <input type="hidden" name="{{ $key }}[{{ $i }}][{{ $subKey }}]" value="{{ $subVal }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endif
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if (!auth()->check())
                        <p class="small text-muted mb-2"><a href="{{ route('login') }}">Log in</a> to save permanently.</p>
                    @endif
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Save This Estimate
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
