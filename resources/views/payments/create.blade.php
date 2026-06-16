<x-app-layout>
    <x-slot name="pageTitle">Receive Payment</x-slot>
    <x-slot name="pageBreadcrumb">Home / Payments / Receive</x-slot>

    <section class="module-page customer-entry-page payments-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('payments.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Payments
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @if ($errors->any())
                <div class="form-validation-summary" role="alert">
                    <strong>Please fix the highlighted fields.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="GET" action="{{ route('payments.create') }}" class="customer-form customer-entry-form payment-customer-picker">
                <div class="customer-form-body">
                    <section class="customer-form-section">
                        <div class="section-title-row">
                            <h2>Search Customer</h2>
                        </div>

                        <div class="customer-form-grid">
                            <div class="form-field">
                                <label for="customer_id_filter" class="form-label">Customer <span class="required">*</span></label>
                                <div class="input-shell select-shell">
                                    <select id="customer_id_filter" name="customer_id" class="form-control form-select" required>
                                        <option value="">Search or select customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>
                                                {{ $customer->full_name }} - {{ $customer->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="customer-form-footer">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-brand save-customer-btn">
                            <span>Show Pending Invoices</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($selectedCustomer)
            @php
                $invoiceRemainingAmount = $invoices->sum('remaining_amount');
            @endphp
            <div class="form-card customer-entry-card payment-receive-card">
                <form method="POST" action="{{ route('payments.store') }}" class="customer-form customer-entry-form payment-confirm-form" data-payment-form>
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                    <input type="hidden" name="allocation_mode" value="auto">

                    <div class="customer-form-body">
                        <section class="customer-form-section">
                            <div class="section-title-row">
                                <h2>Receive Payment</h2>
                            </div>

                            <div class="customer-form-grid">
                                <div class="form-field">
                                    <label class="form-label">Customer</label>
                                    <div class="input-shell">
                                        <input type="text" value="{{ $selectedCustomer->full_name }} - {{ $selectedCustomer->phone }}" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-field">
                                    <label for="total_payment_amount" class="form-label">Received Amount <span class="required">*</span></label>
                                    <div class="input-shell @error('total_payment_amount') is-invalid @enderror">
                                        <span class="input-icon">AED</span>
                                        <input id="total_payment_amount" type="number" min="0.01" step="0.01" name="total_payment_amount" value="{{ old('total_payment_amount') }}" class="form-control" data-payment-amount required>
                                    </div>
                                    @error('total_payment_amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="payment_method" class="form-label">Payment Method <span class="required">*</span></label>
                                    <div class="input-shell select-shell @error('payment_method') is-invalid @enderror">
                                        <select id="payment_method" name="payment_method" class="form-control form-select" required>
                                            @foreach ($methods as $value => $label)
                                                <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('payment_method')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="payment_date" class="form-label">Payment Date <span class="required">*</span></label>
                                    <div class="input-shell @error('payment_date') is-invalid @enderror">
                                        <input id="payment_date" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control" required>
                                    </div>
                                    @error('payment_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="customer-form-section">
                            <div class="section-title-row">
                                <h2>Pending Invoices</h2>
                            </div>

                            @error('allocations')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="table-scroll payment-allocation-table">
                                <table class="module-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Total Pending</th>
                                            <th>Received</th>
                                            <th>Remaining</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($invoices->isNotEmpty())
                                            <tr data-payment-summary-row data-total-pending="{{ $invoiceRemainingAmount }}">
                                                <td>
                                                    <div class="customer-name">
                                                        <strong>{{ $selectedCustomer->full_name }}</strong>
                                                        <small>{{ $selectedCustomer->phone ?: 'No phone' }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($invoiceRemainingAmount) }}</td>
                                                <td class="text-end" data-payment-summary-received>AED 0</td>
                                                <td class="text-end" data-payment-summary-remaining>{{ \App\Helpers\CurrencyHelper::format($invoiceRemainingAmount) }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="4" class="no-results-cell">No pending sale or repair invoices for this customer.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="customer-form-section">
                            <div class="section-title-row">
                                <h2>Notes</h2>
                            </div>

                            <div class="form-field form-field-full">
                                <label for="notes" class="form-label">Notes</label>
                                <div class="input-shell textarea-shell @error('notes') is-invalid @enderror">
                                    <textarea id="notes" name="notes" rows="4" maxlength="3000" class="form-control" placeholder="Add payment notes...">{{ old('notes') }}</textarea>
                                </div>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </section>
                    </div>

                    <div class="customer-form-footer">
                        <div class="form-actions">
                            <a href="{{ route('payments.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-brand save-customer-btn" @disabled($invoices->isEmpty())>
                                <span>Receive Payment</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </section>
</x-app-layout>
