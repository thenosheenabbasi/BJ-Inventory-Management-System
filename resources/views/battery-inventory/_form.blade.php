<form method="POST" action="{{ $action }}" class="customer-form customer-entry-form inventory-entry-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="form-validation-summary" role="alert">
            <strong>Please fix the highlighted fields.</strong>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="customer-form-body">
        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Basic Info</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="brand" class="form-label">Brand <span class="required">*</span></label>
                    <div class="input-shell @error('brand') is-invalid @enderror">
                        <input id="brand" type="text" name="brand" value="{{ old('brand', $battery->brand) }}" class="form-control" placeholder="Dell, HP, Lenovo..." maxlength="120" required @error('brand') aria-invalid="true" @enderror>
                    </div>
                    @error('brand')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="model" class="form-label">Model <span class="required">*</span></label>
                    <div class="input-shell @error('model') is-invalid @enderror">
                        <input id="model" type="text" name="model" value="{{ old('model', $battery->model) }}" class="form-control" placeholder="A41N1423, LA04..." maxlength="160" required @error('model') aria-invalid="true" @enderror>
                    </div>
                    @error('model')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="condition" class="form-label">Condition <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('condition') is-invalid @enderror">
                        <select id="condition" name="condition" class="form-control form-select" required @error('condition') aria-invalid="true" @enderror>
                            @foreach ($conditions as $value => $label)
                                <option value="{{ $value }}" @selected(old('condition', $battery->condition ?: 'new') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('condition')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('status') is-invalid @enderror">
                        <select id="status" name="status" class="form-control form-select" required @error('status') aria-invalid="true" @enderror>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $battery->status ?: 'active') === $value)>{{ $label }}</option>
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
                <h2>Pricing &amp; Stock</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="purchase_price" class="form-label">Purchase Price (AED) <span class="required">*</span></label>
                    <div class="input-shell @error('purchase_price') is-invalid @enderror">
                        <input id="purchase_price" type="number" min="0" step="0.01" inputmode="decimal" name="purchase_price" value="{{ old('purchase_price', $battery->purchase_price ?? 0) }}" class="form-control" required @error('purchase_price') aria-invalid="true" @enderror>
                    </div>
                    @error('purchase_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="sale_price" class="form-label">Sale Price (AED) <span class="required">*</span></label>
                    <div class="input-shell @error('sale_price') is-invalid @enderror">
                        <input id="sale_price" type="number" min="0" step="0.01" inputmode="decimal" name="sale_price" value="{{ old('sale_price', $battery->sale_price ?? 0) }}" class="form-control" required @error('sale_price') aria-invalid="true" @enderror>
                    </div>
                    @error('sale_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="stock_quantity" class="form-label">Stock Quantity <span class="required">*</span></label>
                    <div class="input-shell @error('stock_quantity') is-invalid @enderror">
                        <input id="stock_quantity" type="number" min="0" inputmode="numeric" name="stock_quantity" value="{{ old('stock_quantity', $battery->stock_quantity ?? 0) }}" class="form-control" required @error('stock_quantity') aria-invalid="true" @enderror>
                    </div>
                    @error('stock_quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="low_stock_alert_quantity" class="form-label">Low Stock Alert <span class="required">*</span></label>
                    <div class="input-shell @error('low_stock_alert_quantity') is-invalid @enderror">
                        <input id="low_stock_alert_quantity" type="number" min="0" inputmode="numeric" name="low_stock_alert_quantity" value="{{ old('low_stock_alert_quantity', $battery->low_stock_alert_quantity ?? 0) }}" class="form-control" required @error('low_stock_alert_quantity') aria-invalid="true" @enderror>
                    </div>
                    @error('low_stock_alert_quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Warranty &amp; Supplier</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="warranty_days" class="form-label">Warranty Days <span class="required">*</span></label>
                    <div class="input-shell @error('warranty_days') is-invalid @enderror">
                        <input id="warranty_days" type="number" min="0" inputmode="numeric" name="warranty_days" value="{{ old('warranty_days', $battery->warranty_days ?? 0) }}" class="form-control" required @error('warranty_days') aria-invalid="true" @enderror>
                    </div>
                    @error('warranty_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <div class="input-shell select-shell @error('supplier_id') is-invalid @enderror">
                        <select id="supplier_id" name="supplier_id" class="form-control form-select">
                            <option value="">No supplier selected</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $battery->supplier_id) === (string) $supplier->id)>{{ $supplier->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('supplier_id')
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
                    <textarea id="notes" name="notes" rows="4" maxlength="3000" class="form-control" placeholder="Add purchase, warranty, or stock notes..." @error('notes') aria-invalid="true" @enderror>{{ old('notes', $battery->notes) }}</textarea>
                </div>
                @error('notes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </section>
    </div>

    <div class="customer-form-footer">
        <div class="form-actions">
            <a href="{{ route('battery-inventory.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand save-customer-btn">
                <span>{{ $submitLabel }}</span>
            </button>
        </div>
    </div>
</form>
