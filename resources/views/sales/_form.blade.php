@php
    $submittedItems = old('items');
    $rows = $submittedItems
        ? collect($submittedItems)->map(fn ($item) => (object) [
            'battery_inventory_id' => $item['battery_inventory_id'] ?? null,
            'quantity' => $item['quantity'] ?? 1,
            'unit_price' => $item['unit_price'] ?? 0,
            'total_price' => $item['total_price'] ?? 0,
        ])
        : $saleItems;
@endphp

<form method="POST" action="{{ $action }}" class="customer-form customer-entry-form sales-entry-form important-action-form" data-sales-form>
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
                <h2>Customer Information</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="customer_id" class="form-label">Customer <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('customer_id') is-invalid @enderror">
                        <select id="customer_id" name="customer_id" class="form-control form-select" required>
                            <option value="">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $sale->customer_id) === (string) $customer->id)>
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
                    <label for="payment_status" class="form-label">Payment Status <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('payment_status') is-invalid @enderror">
                        <select id="payment_status" name="payment_status" class="form-control form-select" required>
                            @foreach ($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_status', $sale->payment_status ?: 'pending') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('payment_status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Products</h2>
            </div>

            @error('items')
                <div class="invalid-feedback d-block sales-items-error">{{ $message }}</div>
            @enderror

            <div class="sales-products-table" data-sales-items>
                <div class="sales-product-heading">
                    <span>Battery</span>
                    <span>Quantity</span>
                    <span>Unit Price</span>
                    <span>Total</span>
                    <span></span>
                </div>

                @foreach ($rows as $index => $item)
                    <div class="sales-product-row" data-sales-row data-original-battery="{{ $item->battery_inventory_id }}" data-current-quantity="{{ (int) ($item->quantity ?? 0) }}">
                        <div class="form-field">
                            <label class="form-label sales-mobile-label" for="items_{{ $index }}_battery">Battery <span class="required">*</span></label>
                            <div class="input-shell select-shell @error("items.$index.battery_inventory_id") is-invalid @enderror">
                                <select id="items_{{ $index }}_battery" name="items[{{ $index }}][battery_inventory_id]" class="form-control form-select" data-sales-battery required>
                                    <option value="">Select battery</option>
                                    @foreach ($batteries as $battery)
                                        <option
                                            value="{{ $battery->id }}"
                                            data-price="{{ $battery->sale_price }}"
                                            data-stock="{{ $battery->stock_quantity }}"
                                            @selected((string) ($item->battery_inventory_id ?? '') === (string) $battery->id)
                                        >
                                            {{ $battery->brand }} {{ $battery->model }} ({{ $battery->battery_code }}) - Stock: {{ $battery->stock_quantity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error("items.$index.battery_inventory_id")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label class="form-label sales-mobile-label" for="items_{{ $index }}_quantity">Quantity <span class="required">*</span></label>
                            <div class="input-shell @error("items.$index.quantity") is-invalid @enderror">
                                <input id="items_{{ $index }}_quantity" type="number" min="1" step="1" name="items[{{ $index }}][quantity]" value="{{ $item->quantity ?? 1 }}" class="form-control" data-sales-quantity required>
                            </div>
                            @error("items.$index.quantity")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label class="form-label sales-mobile-label" for="items_{{ $index }}_unit_price">Unit Price</label>
                            <div class="input-shell">
                                <span class="input-icon">AED</span>
                                <input id="items_{{ $index }}_unit_price" type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price ?? 0 }}" class="form-control" data-sales-unit-price readonly>
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="form-label sales-mobile-label" for="items_{{ $index }}_total_price">Total</label>
                            <div class="input-shell">
                                <span class="input-icon">AED</span>
                                <input id="items_{{ $index }}_total_price" type="number" min="0" step="0.01" name="items[{{ $index }}][total_price]" value="{{ $item->total_price ?? 0 }}" class="form-control" data-sales-line-total readonly>
                            </div>
                        </div>

                        <button type="button" class="action-btn icon-action danger sales-remove-row" title="Remove product" aria-label="Remove product" data-sales-remove-row>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-light sales-add-row" data-sales-add-row>Add More Product</button>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Summary</h2>
            </div>

            <div class="customer-form-grid sales-summary-grid">
                <div class="form-field">
                    <label for="subtotal" class="form-label">Subtotal</label>
                    <div class="input-shell">
                        <span class="input-icon">AED</span>
                        <input id="subtotal" type="number" min="0" step="0.01" value="{{ old('subtotal', $sale->subtotal ?? 0) }}" class="form-control" data-sales-subtotal readonly>
                    </div>
                </div>

                <div class="form-field">
                    <label for="discount" class="form-label">Discount</label>
                    <div class="input-shell @error('discount') is-invalid @enderror">
                        <span class="input-icon">AED</span>
                        <input id="discount" type="number" min="0" step="0.01" name="discount" value="{{ old('discount', $sale->discount ?? 0) }}" class="form-control" data-sales-discount>
                    </div>
                    @error('discount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="vat" class="form-label">VAT</label>
                    <div class="input-shell @error('vat') is-invalid @enderror">
                        <span class="input-icon">AED</span>
                        <input id="vat" type="number" min="0" step="0.01" name="vat" value="{{ old('vat', $sale->vat ?? 0) }}" class="form-control" data-sales-vat>
                    </div>
                    @error('vat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="grand_total" class="form-label">Grand Total</label>
                    <div class="input-shell sales-grand-total-shell">
                        <span class="input-icon">AED</span>
                        <input id="grand_total" type="number" min="0" step="0.01" value="{{ old('total_amount', $sale->total_amount ?? 0) }}" class="form-control" data-sales-grand-total readonly>
                    </div>
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
                    <textarea id="notes" name="notes" rows="4" maxlength="3000" class="form-control" placeholder="Add sale notes...">{{ old('notes', $sale->notes) }}</textarea>
                </div>
                @error('notes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </section>
    </div>

    <div class="customer-form-footer">
        <div class="form-actions">
            <a href="{{ route('sales.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand save-customer-btn">
                <span>{{ $submitLabel }}</span>
            </button>
        </div>
    </div>
</form>
