<form method="POST" action="{{ $action }}" class="customer-form customer-entry-form repair-entry-form" data-repair-calculator>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="customer-form-body">
        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Repair Information</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="customer_id" class="form-label">Customer <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('customer_id') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <select id="customer_id" name="customer_id" class="form-control form-select" required>
                            <option value="">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $repairJob->customer_id) === (string) $customer->id)>
                                    {{ $customer->full_name }} - {{ $customer->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('customer_id')
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
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $repairJob->status ?: 'received') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field form-field-full">
                    <label for="battery_details" class="form-label">Battery Model / Name <span class="required">*</span></label>
                    <div class="input-shell @error('battery_details') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="18" height="10" rx="2"/><path d="M22 11v2"/><path d="M7 12h5"/></svg>
                        </span>
                        <input id="battery_details" type="text" name="battery_details" value="{{ old('battery_details', $repairJob->battery_details) }}" class="form-control" placeholder="Enter battery model or name" maxlength="255" required>
                    </div>
                    @error('battery_details')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field form-field-full">
                    <label for="issue_description" class="form-label">Issue Description</label>
                    <div class="input-shell textarea-shell @error('issue_description') is-invalid @enderror">
                        <span class="input-icon textarea-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        </span>
                        <textarea id="issue_description" name="issue_description" rows="3" class="form-control" placeholder="Describe the customer complaint and observed problem...">{{ old('issue_description', $repairJob->issue_description) }}</textarea>
                    </div>
                    @error('issue_description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Cost &amp; Delivery</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                    <div class="input-shell @error('quantity') is-invalid @enderror">
                        <span class="input-icon">Qty</span>
                        <input id="quantity" type="number" min="1" step="1" name="quantity" value="{{ old('quantity', $repairJob->quantity ?? 1) }}" class="form-control" data-repair-quantity required>
                    </div>
                    @error('quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="unit_price" class="form-label">Unit Price <span class="required">*</span></label>
                    <div class="input-shell @error('unit_price') is-invalid @enderror">
                        <span class="input-icon">AED</span>
                        <input id="unit_price" type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price', $repairJob->unit_price ?? 0) }}" class="form-control" data-repair-unit-price required>
                    </div>
                    @error('unit_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="estimated_cost" class="form-label">Amount <span class="required">*</span></label>
                    <div class="input-shell @error('estimated_cost') is-invalid @enderror">
                        <span class="input-icon">AED</span>
                        <input id="estimated_cost" type="number" min="0" step="0.01" name="estimated_cost" value="{{ old('estimated_cost', $repairJob->estimated_cost ?? 0) }}" class="form-control" data-repair-total readonly required>
                    </div>
                    @error('estimated_cost')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="advance_payment" class="form-label">Advance Payment <span class="required">*</span></label>
                    <div class="input-shell @error('advance_payment') is-invalid @enderror">
                        <span class="input-icon">AED</span>
                        <input id="advance_payment" type="number" min="0" step="0.01" name="advance_payment" value="{{ old('advance_payment', $repairJob->advance_payment ?? 0) }}" class="form-control" required>
                    </div>
                    @error('advance_payment')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                    <div class="input-shell @error('expected_delivery_date') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </span>
                        <input id="expected_delivery_date" type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', $repairJob->expected_delivery_date?->format('Y-m-d')) }}" class="form-control">
                    </div>
                    @error('expected_delivery_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if ($method !== 'POST')
                    <div class="form-field form-field-full">
                        <label for="status_note" class="form-label">Status Change Note</label>
                        <div class="input-shell textarea-shell @error('status_note') is-invalid @enderror">
                            <span class="input-icon textarea-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                            </span>
                            <textarea id="status_note" name="status_note" rows="3" class="form-control" placeholder="Optional note for the timeline when status changes...">{{ old('status_note') }}</textarea>
                        </div>
                        @error('status_note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="customer-form-footer">
        <div class="form-actions">
            <a href="{{ route('repair-jobs.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand save-customer-btn">
                <span>{{ $submitLabel }}</span>
            </button>
        </div>
    </div>
</form>
