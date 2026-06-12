{{-- Shared KYC submission form. Expects $action (submit route URL) and optional $kyc. --}}
<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-semibold">Verification type</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" id="kycType">
            @foreach(\App\Enums\KYCType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $kyc->type->value ?? 'individual') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full legal name</label>
            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $kyc->full_name ?? '') }}">
            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Date of birth</label>
            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', optional($kyc->date_of_birth ?? null)->format('Y-m-d')) }}">
            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Business-only fields --}}
    <div class="row g-3 mt-0 kyc-business d-none">
        <div class="col-md-6">
            <label class="form-label">Business name</label>
            <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name', $kyc->business_name ?? '') }}">
            @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">RC number</label>
            <input type="text" name="rc_number" class="form-control @error('rc_number') is-invalid @enderror" value="{{ old('rc_number', $kyc->rc_number ?? '') }}">
            @error('rc_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">ID document type</label>
            <select name="id_type" class="form-select @error('id_type') is-invalid @enderror">
                @foreach(\App\Enums\KYCDocumentType::cases() as $doc)
                    <option value="{{ $doc->value }}" @selected(old('id_type', $kyc->id_type->value ?? '') === $doc->value)>{{ $doc->label() }}</option>
                @endforeach
            </select>
            @error('id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">ID number</label>
            <input type="text" name="id_number" class="form-control @error('id_number') is-invalid @enderror" value="{{ old('id_number', $kyc->id_number ?? '') }}">
            @error('id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $kyc->address ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $kyc->city ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state', $kyc->state ?? '') }}">
        </div>
    </div>

    <hr class="my-4">

    <p class="fw-semibold mb-2">Documents</p>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">ID document — front</label>
            <input type="file" name="document_front" class="form-control @error('document_front') is-invalid @enderror">
            @error('document_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">ID document — back <span class="text-muted">(if applicable)</span></label>
            <input type="file" name="document_back" class="form-control @error('document_back') is-invalid @enderror">
            @error('document_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Selfie holding your ID</label>
            <input type="file" name="selfie" class="form-control @error('selfie') is-invalid @enderror">
            @error('selfie')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Proof of address <span class="text-muted">(optional)</span></label>
            <input type="file" name="proof_of_address" class="form-control @error('proof_of_address') is-invalid @enderror">
            @error('proof_of_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-text mt-2">Accepted: JPG, PNG, PDF · max 5MB each. Your documents are stored securely and only seen by our verification team.</div>

    <button type="submit" class="btn btn-primary mt-4">Submit for Verification</button>
</form>

<script>
    (function () {
        const typeSelect = document.getElementById('kycType');
        const business = document.querySelector('.kyc-business');
        function toggle() { business.classList.toggle('d-none', typeSelect.value !== 'business'); }
        typeSelect?.addEventListener('change', toggle);
        toggle();
    })();
</script>
