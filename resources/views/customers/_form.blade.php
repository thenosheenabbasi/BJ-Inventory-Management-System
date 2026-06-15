<form method="POST" action="{{ $action }}" class="customer-form customer-entry-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="customer-form-body">
        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Basic Information</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                    <div class="input-shell @error('full_name') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $customer->full_name) }}" class="form-control" placeholder="Enter full name" required>
                    </div>
                    @error('full_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="phone" class="form-label">Phone <span class="required">*</span></label>
                    <div class="input-shell @error('phone') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3.08 5.18 2 2 0 0 1 5.06 3h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.59 2.61a2 2 0 0 1-.45 2.11L9 10.64a16 16 0 0 0 4.36 4.36l1.2-1.2a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.61.59A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control" placeholder="Enter phone number" required>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <div class="input-shell @error('whatsapp') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l1.65-3.8A8.5 8.5 0 1 1 7 19.35L3 21z"/><path d="M9.5 8.75c.25 2.4 1.65 4 4 4.75l1.25-1.1 2.1.5-.35 2.1c-.1.55-.55.95-1.1.95-4.35 0-7.85-3.5-7.85-7.85 0-.55.4-1 .95-1.1l2.1-.35.5 2.1-1.6 0z"/></svg>
                        </span>
                        <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $customer->whatsapp) }}" class="form-control" placeholder="Enter WhatsApp number">
                    </div>
                    @error('whatsapp')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-shell @error('email') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control" placeholder="Enter email address">
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Location &amp; Classification</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="city" class="form-label">City</label>
                    <div class="input-shell @error('city') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v16"/><path d="M9 21v-4h3v4"/><path d="M8 7h1M12 7h1M8 11h1M12 11h1M19 21v-8h1"/></svg>
                        </span>
                        <input id="city" type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control" placeholder="Enter city">
                    </div>
                    @error('city')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="country" class="form-label">Country <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('country') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/></svg>
                        </span>
                        <select id="country" name="country" class="form-control form-select" required>
                            @foreach (['UAE', 'Saudi Arabia', 'Qatar', 'Oman', 'Bahrain', 'Kuwait'] as $country)
                                <option value="{{ $country }}" @selected(old('country', $customer->country ?: 'UAE') === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('country')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="customer_type" class="form-label">Customer Type <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('customer_type') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0l-7.2-7.2A2 2 0 0 1 2.8 12V5a2 2 0 0 1 2-2h7a2 2 0 0 1 1.4.6l7.4 7.4a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r=".5"/></svg>
                        </span>
                        <select id="customer_type" name="customer_type" class="form-control form-select" required>
                            <option value="" @selected(! old('customer_type', $customer->customer_type))>Select customer type</option>
                            @foreach ($customerTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('customer_type', $customer->customer_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('customer_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('status') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                        </span>
                        <select id="status" name="status" class="form-control form-select" required>
                            <option value="" @selected(! old('status', $customer->status))>Select status</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $customer->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Notes</h2>
            </div>

            <div class="form-field form-field-full">
                <label for="notes" class="form-label">Notes</label>
                <div class="input-shell textarea-shell @error('notes') is-invalid @enderror">
                    <span class="input-icon textarea-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </span>
                    <textarea id="notes" name="notes" rows="4" class="form-control" placeholder="Add any additional notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                </div>
                @error('notes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </section>
    </div>

    <div class="customer-form-footer">
        <div class="form-actions">
            <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand save-customer-btn">
                <span>{{ $submitLabel }}</span>
            </button>
        </div>
    </div>
</form>
