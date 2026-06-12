<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Basic Info</h5>
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
            <input type="text" name="display_name" id="display_name"
                value="{{ old('display_name', $profile->display_name ?? '') }}"
                class="form-control @error('display_name') is-invalid @enderror"
                placeholder="e.g. Dr. Amara Okonkwo" required>
            @error('display_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="niche" class="form-label">Niche / Specialty <span class="text-danger">*</span></label>
            <input type="text" name="niche" id="niche"
                value="{{ old('niche', $profile->niche ?? '') }}"
                class="form-control @error('niche') is-invalid @enderror"
                placeholder="e.g. FinTech Startups, SaaS Growth, E-Commerce" required>
            @error('niche') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="experience_years" class="form-label">Years of Experience <span class="text-danger">*</span></label>
            <input type="number" name="experience_years" id="experience_years"
                value="{{ old('experience_years', $profile->experience_years ?? '') }}"
                class="form-control @error('experience_years') is-invalid @enderror"
                min="1" max="50" required>
            @error('experience_years') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="expertise" class="form-label">Areas of Expertise</label>
            <input type="text" name="expertise" id="expertise"
                value="{{ old('expertise', isset($profile) ? implode(', ', (array) $profile->expertise) : '') }}"
                class="form-control @error('expertise') is-invalid @enderror"
                placeholder="Comma-separated: Fundraising, MVP Development, Marketing">
            <div class="form-text">Separate skills with commas.</div>
            @error('expertise') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Bio</h5>
        <div class="mb-3">
            <textarea name="bio" id="bio" rows="6"
                class="form-control @error('bio') is-invalid @enderror"
                placeholder="Tell potential clients about your background, achievements, and how you can help them…" required>{{ old('bio', $profile->bio ?? '') }}</textarea>
            <div class="form-text">Min 50 characters. Be specific about your experience and results.</div>
            @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Links</h5>
        <div class="mb-3">
            <label for="linkedin" class="form-label">LinkedIn URL</label>
            <input type="url" name="linkedin" id="linkedin"
                value="{{ old('linkedin', $profile->linkedin ?? '') }}"
                class="form-control @error('linkedin') is-invalid @enderror"
                placeholder="https://linkedin.com/in/yourprofile">
            @error('linkedin') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-0">
            <label for="calendly_url" class="form-label">Calendly URL <span class="text-muted">(optional)</span></label>
            <input type="url" name="calendly_url" id="calendly_url"
                value="{{ old('calendly_url', $profile->calendly_url ?? '') }}"
                class="form-control @error('calendly_url') is-invalid @enderror"
                placeholder="https://calendly.com/yourhandle">
            <div class="form-text">Clients can use this as an alternative booking method.</div>
            @error('calendly_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
