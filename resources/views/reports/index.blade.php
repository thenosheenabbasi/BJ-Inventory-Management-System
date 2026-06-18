<x-app-layout>
    <x-slot name="pageTitle">Payment Summary Report</x-slot>
    <x-slot name="pageBreadcrumb">Home / Reports / Payment Summary Report</x-slot>

    <section class="module-page erp-report-page">
        <div class="erp-report-commandbar">
            <a
                href="{{ route('reports.pdf', request()->only(['customer_id', 'start_date', 'end_date'])) }}"
                class="btn erp-report-download"
                title="Download PDF"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3v12"></path>
                    <path d="m7 10 5 5 5-5"></path>
                    <path d="M5 21h14"></path>
                </svg>
                <span>Download PDF</span>
            </a>

            <form method="GET" action="{{ route('reports.index') }}" class="erp-report-filter">
                <div class="erp-report-field erp-report-customer">
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id" class="form-control form-select">
                        <option value="">All Customers</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>
                                {{ $customer->customer_code }} - {{ $customer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="erp-report-field">
                    <label for="start_date">From Date</label>
                    <input id="start_date" type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="form-control">
                </div>
                <div class="erp-report-field">
                    <label for="end_date">To Date</label>
                    <input id="end_date" type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="form-control">
                </div>
                <div class="erp-report-actions">
                    <button type="submit" class="btn btn-brand">Apply Filter</button>
                    <a href="{{ route('reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>

        <div class="erp-report-sheet">
            <header class="erp-report-header">
                <div>
                    <p class="erp-report-company">M. Bilal jamshed</p>
                    <h2>Payment Summary Report</h2>
                    <p>Inventory Management System</p>
                </div>
                <div class="erp-report-header-values">
                    <strong>{{ $selectedCustomer?->full_name ?: 'All Customers' }}</strong>
                    <span>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</span>
                    <span>{{ $generatedAt->format('d M Y, h:i A') }}</span>
                </div>
            </header>

            <section class="erp-report-section erp-financial-summary">
                <div class="erp-report-section-title">
                    <div>
                        <h3>Financial Summary</h3>
                        <p>Payment position for the selected report</p>
                    </div>
                    <span>{{ number_format($summary['sales_count'] + $summary['repair_count']) }} invoices</span>
                </div>
                <div class="erp-financial-totals">
                    <div>
                        <span>Total Invoiced</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($summary['gross_business']) }}</strong>
                    </div>
                    <div>
                        <span>Total Received</span>
                        <strong class="is-received">{{ \App\Helpers\CurrencyHelper::format($summary['gross_received']) }}</strong>
                    </div>
                    <div>
                        <span>Outstanding Balance</span>
                        <strong class="is-pending">{{ \App\Helpers\CurrencyHelper::format($summary['gross_remaining']) }}</strong>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="erp-financial-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Invoices</th>
                                <th class="text-end">Invoiced</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Sale Battery</strong></td>
                                <td class="text-end">{{ number_format($summary['sales_count']) }}</td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($summary['sales_total']) }}</td>
                                <td class="text-end is-received">{{ \App\Helpers\CurrencyHelper::format($summary['sales_received']) }}</td>
                                <td class="text-end is-pending">{{ \App\Helpers\CurrencyHelper::format($summary['sales_remaining']) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Repair Battery</strong></td>
                                <td class="text-end">{{ number_format($summary['repair_count']) }}</td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($summary['repair_total']) }}</td>
                                <td class="text-end is-received">{{ \App\Helpers\CurrencyHelper::format($summary['repair_paid']) }}</td>
                                <td class="text-end is-pending">{{ \App\Helpers\CurrencyHelper::format($summary['repair_remaining']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="erp-report-section erp-report-detail-section">
                <div class="erp-report-section-title">
                    <h3>Sale Battery Invoices</h3>
                    <span>{{ number_format($saleDetails->count()) }} records</span>
                </div>
                <div class="table-scroll erp-report-table-scroll">
                    <table class="erp-report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Battery Details</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($saleDetails as $sale)
                                <tr>
                                    <td>{{ $sale->created_at?->format('d M Y') ?: '-' }}</td>
                                    <td><strong class="erp-report-code">{{ $sale->sale_number }}</strong></td>
                                    <td>{{ $sale->customer?->full_name ?: '-' }}</td>
                                    <td>
                                        @forelse ($sale->items as $item)
                                            <div>{{ trim(($item->battery?->brand ?: '').' '.($item->battery?->model ?: '')) ?: 'Battery removed' }}</div>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="text-end">
                                        @forelse ($sale->items as $item)
                                            <div>{{ number_format($item->quantity) }}</div>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($sale->received_amount) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($sale->remaining_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="erp-report-empty">No sale battery records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="erp-report-section erp-report-detail-section">
                <div class="erp-report-section-title">
                    <h3>Repair Battery Invoices</h3>
                    <span>{{ number_format($repairDetails->count()) }} records</span>
                </div>
                <div class="table-scroll erp-report-table-scroll">
                    <table class="erp-report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Battery Details</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($repairDetails as $repair)
                                <tr>
                                    <td>{{ $repair->created_at?->format('d M Y') ?: '-' }}</td>
                                    <td><strong class="erp-report-code">{{ $repair->repair_number }}</strong></td>
                                    <td>{{ $repair->customer?->full_name ?: '-' }}</td>
                                    <td>{{ $repair->battery_details ?: '-' }}</td>
                                    <td class="text-end">{{ number_format($repair->quantity ?: 1) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($repair->estimated_cost) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($repair->paidAmount()) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($repair->remainingAmount()) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="erp-report-empty">No repair battery records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="erp-report-section erp-report-detail-section">
                <div class="erp-report-section-title">
                    <h3>Payment Received Record</h3>
                    <span>{{ number_format($paymentDetails->count()) }} entries</span>
                </div>
                <div class="table-scroll erp-report-table-scroll">
                    <table class="erp-report-table">
                        <thead>
                            <tr>
                                <th>Received Date</th>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th>Invoices Paid</th>
                                <th>Method</th>
                                <th class="text-end">Amount Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentDetails as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('d M Y') ?: $payment->created_at?->format('d M Y') }}</td>
                                    <td><strong class="erp-report-code">{{ $payment->code() }}</strong></td>
                                    <td>{{ $payment->customer?->full_name ?: '-' }}</td>
                                    <td>
                                        @forelse ($payment->allocations as $allocation)
                                            <div>{{ $allocation->documentNumber() }} - {{ \App\Helpers\CurrencyHelper::format($allocation->allocated_amount) }}</div>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td>{{ $payment->methodLabel() }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($payment->receivedAmount()) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="erp-report-empty">No payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="erp-report-footer">
                <span>Generated by M. Bilal jamshed</span>
            </footer>
        </div>
    </section>
</x-app-layout>
