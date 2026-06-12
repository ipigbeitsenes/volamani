@extends('layouts.app')

@section('title', 'Business Pricing Calculator')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Business Pricing Calculator</h2>
                <p class="text-muted lead">Get accurate pricing estimates for digital services in Nigeria. Stop undercharging.</p>
            </div>

            {{-- Step 1: Category --}}
            <div class="card border-0 shadow-sm mb-4" id="step-category">
                <div class="card-body">
                    <h5 class="card-title mb-3"><span class="badge bg-primary me-2">1</span>Select Service Category</h5>
                    <div class="row g-3" id="category-grid">
                        @foreach ($categories as $cat)
                            <div class="col-6 col-md-4 col-lg-3">
                                <button type="button"
                                    class="btn btn-outline-secondary w-100 h-100 category-btn py-3"
                                    data-value="{{ $cat['value'] }}"
                                    data-label="{{ $cat['label'] }}">
                                    <i class="{{ $cat['icon'] }} d-block fs-3 mb-1"></i>
                                    <span class="small fw-semibold">{{ $cat['label'] }}</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Steps 2–5: Calculator form (hidden until category selected) --}}
            <div id="calculator-form-wrapper" style="display:none">
                <form id="pricing-form" method="POST" action="{{ route('pricing-calculator.calculate') }}">
                    @csrf
                    <input type="hidden" name="category" id="input-category">

                    {{-- Step 2: Service info & pricing type --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><span class="badge bg-primary me-2">2</span>Service Details</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Service Name</label>
                                <input type="text" name="service_name" id="service_name"
                                    class="form-control" placeholder="e.g. E-commerce website for Amaka's Fashion Store" required>
                            </div>

                            {{-- Template picker --}}
                            <div class="mb-3" id="template-section">
                                <label class="form-label fw-semibold">Start from a Template <span class="text-muted fw-normal">(optional)</span></label>
                                <div id="template-cards" class="row g-2">
                                    <div class="col-12 text-muted small">Loading templates…</div>
                                </div>
                                <input type="hidden" name="template_id" id="input-template-id">
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold">Pricing Model</label>
                                <div class="row g-2" id="pricing-type-options">
                                    @foreach ($pricingTypes as $type)
                                        <div class="col-md-4">
                                            <div class="form-check border rounded p-3 pricing-type-card" data-value="{{ $type->value }}">
                                                <input class="form-check-input" type="radio" name="pricing_type"
                                                    id="pt_{{ $type->value }}" value="{{ $type->value }}"
                                                    @checked($loop->first)>
                                                <label class="form-check-label d-block" for="pt_{{ $type->value }}">
                                                    <span class="fw-semibold">{{ $type->label() }}</span>
                                                    <div class="text-muted small mt-1">{{ $type->description() }}</div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Pricing inputs (dynamic by type) --}}
                    <div class="card border-0 shadow-sm mb-4" id="step-pricing-inputs">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><span class="badge bg-primary me-2">3</span>Pricing Details</h5>

                            {{-- Fixed pricing --}}
                            <div id="section-fixed">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Project Price (₦)</label>
                                    <input type="number" name="base_price" id="base_price"
                                        class="form-control form-control-lg" placeholder="e.g. 400000" min="0" step="1000">
                                    <div class="form-text">Enter your total fixed price for this project.</div>
                                </div>
                            </div>

                            {{-- Hourly pricing --}}
                            <div id="section-hourly" style="display:none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Hourly Rate (₦)</label>
                                        <input type="number" name="hourly_rate" id="hourly_rate"
                                            class="form-control" placeholder="e.g. 15000" min="0" step="500">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estimated Hours</label>
                                        <input type="number" name="estimated_hours" id="estimated_hours"
                                            class="form-control" placeholder="e.g. 40" min="0.5" step="0.5">
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <strong>Estimated total: </strong>
                                    <span id="hourly-calc-display" class="text-success fw-bold">₦0.00</span>
                                </div>
                            </div>

                            {{-- Milestone pricing --}}
                            <div id="section-milestone" style="display:none">
                                <div id="milestones-list"></div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-milestone-btn">
                                    <i class="bi bi-plus-circle me-1"></i> Add Milestone
                                </button>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <strong>Total across milestones: </strong>
                                    <span id="milestone-total-display" class="text-success fw-bold">₦0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4: Add-ons --}}
                    <div class="card border-0 shadow-sm mb-4" id="step-addons">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><span class="badge bg-primary me-2">4</span>Add-ons & Extras</h5>
                            <div id="addons-grid" class="row g-2">
                                <div class="col-12 text-muted small">Select a category first to see available add-ons.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 5: Urgency --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><span class="badge bg-primary me-2">5</span>Timeline & Urgency</h5>
                            <div class="row g-3">
                                @foreach ([
                                    ['normal', 'Normal', 'Standard timeline', '1.0×', 'secondary'],
                                    ['soon',   'Soon', 'Needed within 2 weeks', '+25%', 'warning'],
                                    ['urgent', 'Urgent', 'Needed within 1 week', '+50%', 'orange'],
                                    ['rush',   'Rush', 'Needed within 48 hours', '+100%', 'danger'],
                                ] as [$val, $label, $desc, $mult, $color])
                                    <div class="col-6 col-md-3">
                                        <div class="form-check border rounded p-3">
                                            <input class="form-check-input urgency-radio" type="radio" name="urgency"
                                                id="urg_{{ $val }}" value="{{ $val }}"
                                                @checked($val === 'normal')>
                                            <label class="form-check-label d-block" for="urg_{{ $val }}">
                                                <span class="fw-semibold">{{ $label }}</span>
                                                <div class="text-muted small">{{ $desc }}</div>
                                                <span class="badge bg-{{ $color }} mt-1">{{ $mult }}</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Optional client info --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-1">Client Info <span class="text-muted fw-normal fs-6">(for quotation)</span></h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" name="client_name" class="form-control" placeholder="Client name (optional)">
                                </div>
                                <div class="col-md-6">
                                    <input type="email" name="client_email" class="form-control" placeholder="Client email (optional)">
                                </div>
                                <div class="col-12">
                                    <textarea name="notes" rows="2" class="form-control" placeholder="Project notes or scope details (optional)…"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Live total & submit --}}
                    <div class="card border-0 shadow bg-primary text-white mb-4" id="live-total-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75">Estimated Total</div>
                                <div class="h2 mb-0 fw-bold" id="live-total-display">₦0.00</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="save" value="0" class="btn btn-light btn-lg px-4">
                                    <i class="bi bi-eye me-1"></i> View Estimate
                                </button>
                                <button type="submit" name="save" value="1" class="btn btn-warning btn-lg px-4">
                                    <i class="bi bi-save me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- My saved estimates (if logged in) --}}
            @if (auth()->check() && $myEstimates && $myEstimates->isNotEmpty())
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Saved Estimates</h5>
                        <a href="{{ route('pricing-calculator.my-estimates') }}" class="btn btn-link btn-sm">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Service</th><th>Category</th><th>Total</th><th>Date</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($myEstimates->take(5) as $est)
                                    <tr>
                                        <td>{{ $est->service_name }}</td>
                                        <td>{{ $est->category->label() }}</td>
                                        <td class="fw-bold text-success">{{ money($est->total) }}</td>
                                        <td class="text-muted small">{{ $est->created_at->format('d M Y') }}</td>
                                        <td><a href="{{ route('pricing-calculator.show', $est->reference) }}" class="btn btn-outline-primary btn-sm">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const categoryBtns    = document.querySelectorAll('.category-btn');
    const formWrapper     = document.getElementById('calculator-form-wrapper');
    const inputCategory   = document.getElementById('input-category');
    const pricingTypeRadios = document.querySelectorAll('input[name="pricing_type"]');
    const urgencyRadios   = document.querySelectorAll('.urgency-radio');
    let currentCategory   = null;
    let addOnPrices       = {};  // id -> kobo
    let milestoneCount    = 0;

    const URGENCY_MULT = { normal: 1.0, soon: 1.25, urgent: 1.5, rush: 2.0 };

    // --- Category selection ---
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            categoryBtns.forEach(b => b.classList.remove('btn-primary', 'active'));
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'active');
            currentCategory = this.dataset.value;
            inputCategory.value = currentCategory;
            formWrapper.style.display = 'block';
            loadCategoryData(currentCategory);
            document.getElementById('calculator-form-wrapper').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // --- Load templates + add-ons via AJAX ---
    function loadCategoryData(category) {
        fetch(`{{ route('pricing-calculator.templates') }}?category=${category}`)
            .then(r => r.json())
            .then(data => {
                renderTemplates(data.templates);
                renderAddOns(data.add_ons);
                recalculate();
            });
    }

    function renderTemplates(templates) {
        const grid = document.getElementById('template-cards');
        if (!templates.length) { grid.innerHTML = '<div class="col-12 text-muted small">No templates for this category.</div>'; return; }
        grid.innerHTML = templates.map(t => `
            <div class="col-sm-6 col-md-4">
                <div class="card border template-card h-100 cursor-pointer" data-template='${JSON.stringify(t)}' style="cursor:pointer">
                    <div class="card-body p-3">
                        <div class="fw-semibold">${t.name}</div>
                        <div class="small text-muted">${t.description || ''}</div>
                        <div class="small text-success fw-bold mt-1">${t.price_range}</div>
                    </div>
                </div>
            </div>`).join('');
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('border-primary'));
                this.classList.add('border-primary');
                applyTemplate(JSON.parse(this.dataset.template));
            });
        });
    }

    function applyTemplate(t) {
        document.getElementById('input-template-id').value = t.id;
        // Set pricing type
        const radio = document.querySelector(`input[name="pricing_type"][value="${t.pricing_type}"]`);
        if (radio) { radio.checked = true; togglePricingSection(t.pricing_type); }
        if (t.pricing_type === 'fixed' && t.base_price) document.getElementById('base_price').value = t.base_price;
        if (t.pricing_type === 'hourly') {
            if (t.hourly_rate) document.getElementById('hourly_rate').value = t.hourly_rate;
            if (t.max_hours) document.getElementById('estimated_hours').value = t.max_hours;
        }
        recalculate();
    }

    function renderAddOns(addOns) {
        const grid = document.getElementById('addons-grid');
        if (!addOns.length) { grid.innerHTML = '<div class="col-12 text-muted small">No add-ons available.</div>'; return; }
        addOnPrices = {};
        addOns.forEach(a => { addOnPrices[a.id] = { price: a.price * 100, is_pct: a.is_percentage }; });
        grid.innerHTML = addOns.map(a => `
            <div class="col-sm-6 col-md-4">
                <div class="form-check border rounded p-3">
                    <input class="form-check-input addon-check" type="checkbox" name="add_on_ids[]" id="ao_${a.id}" value="${a.id}">
                    <label class="form-check-label d-block" for="ao_${a.id}">
                        <span class="fw-semibold small">${a.name}</span>
                        <div class="text-muted small">${a.description || ''}</div>
                        <span class="badge bg-light text-dark border mt-1">+ ${a.display_price}</span>
                    </label>
                </div>
            </div>`).join('');
        document.querySelectorAll('.addon-check').forEach(cb => cb.addEventListener('change', recalculate));
    }

    // --- Pricing type toggle ---
    pricingTypeRadios.forEach(r => r.addEventListener('change', function () {
        togglePricingSection(this.value);
        recalculate();
    }));

    function togglePricingSection(type) {
        ['fixed','hourly','milestone'].forEach(t => {
            document.getElementById(`section-${t}`).style.display = t === type ? 'block' : 'none';
        });
    }
    togglePricingSection('fixed');

    // --- Hourly live calc ---
    ['hourly_rate','estimated_hours'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', recalculate);
    });
    document.getElementById('base_price')?.addEventListener('input', recalculate);
    urgencyRadios.forEach(r => r.addEventListener('change', recalculate));

    // --- Milestones ---
    document.getElementById('add-milestone-btn').addEventListener('click', addMilestone);
    function addMilestone() {
        milestoneCount++;
        const idx = milestoneCount - 1;
        const div = document.createElement('div');
        div.className = 'border rounded p-3 mb-2 milestone-row';
        div.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small">Milestone ${milestoneCount} Name</label>
                    <input type="text" name="milestones[${idx}][name]" class="form-control form-control-sm" placeholder="e.g. Design Phase" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Amount (₦)</label>
                    <input type="number" name="milestones[${idx}][amount]" class="form-control form-control-sm milestone-amount" min="0" step="1000" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Description</label>
                    <input type="text" name="milestones[${idx}][description]" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-milestone"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
        div.querySelector('.milestone-amount').addEventListener('input', recalculate);
        div.querySelector('.remove-milestone').addEventListener('click', function () { div.remove(); recalculate(); });
        document.getElementById('milestones-list').appendChild(div);
    }

    // --- Main recalculate ---
    function formatNaira(kobo) {
        return '₦' + (kobo / 100).toLocaleString('en-NG', { minimumFractionDigits: 2 });
    }

    function recalculate() {
        const type = document.querySelector('input[name="pricing_type"]:checked')?.value || 'fixed';
        const urgency = document.querySelector('.urgency-radio:checked')?.value || 'normal';
        const mult = URGENCY_MULT[urgency] || 1.0;

        let baseKobo = 0;
        if (type === 'fixed') {
            baseKobo = Math.round((parseFloat(document.getElementById('base_price').value) || 0) * 100);
        } else if (type === 'hourly') {
            const rate = Math.round((parseFloat(document.getElementById('hourly_rate').value) || 0) * 100);
            const hrs  = parseFloat(document.getElementById('estimated_hours').value) || 0;
            baseKobo = Math.round(rate * hrs);
            document.getElementById('hourly-calc-display').textContent = formatNaira(baseKobo);
        } else if (type === 'milestone') {
            let total = 0;
            document.querySelectorAll('.milestone-amount').forEach(inp => {
                total += Math.round((parseFloat(inp.value) || 0) * 100);
            });
            baseKobo = total;
            document.getElementById('milestone-total-display').textContent = formatNaira(baseKobo);
        }

        let addOnsKobo = 0;
        document.querySelectorAll('.addon-check:checked').forEach(cb => {
            const ao = addOnPrices[cb.value];
            if (!ao) return;
            addOnsKobo += ao.is_pct ? Math.round(baseKobo * ao.price / 10000) : ao.price;
        });

        const subtotal = baseKobo + addOnsKobo;
        const total    = Math.round(subtotal * mult);
        document.getElementById('live-total-display').textContent = formatNaira(total);
    }
})();
</script>
@endsection
