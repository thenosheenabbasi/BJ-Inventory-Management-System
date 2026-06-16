<x-app-layout>
    <x-slot name="pageTitle">Payments</x-slot>
    <x-slot name="pageBreadcrumb">Home / Payments</x-slot>

    <section class="module-page customers-modern-page payments-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Receive and review customer payments across multiple invoices.</p>
            </div>
            @if ($canCreate)
                <a href="{{ route('payments.create') }}" class="btn btn-brand btn-compact">+ Receive Payment</a>
            @endif
        </div>

        <div class="table-card customer-records-card customers-modern-card">
            <div class="table-card-header customer-records-header customers-modern-card-header">
                @if ($summary['total'] > 0)
                    <form method="GET" action="{{ route('payments.index') }}" class="customer-search-form">
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search payments..." aria-label="Payment search">
                    </form>
                @endif
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table payments-modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Received</th>
                            <th>Remaining</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paymentCustomers as $customer)
                            @php
                                $latestPayment = $customer->latestPaymentRecord();
                                $receivedTotal = $customer->receivedPaymentTotal();
                                $remainingTotal = $customer->outstandingBalanceTotal();
                                $ledgerTotal = $customer->paymentLedgerTotal();
                            @endphp
                            <tr>
                                <td><span class="code-text">{{ $latestPayment?->code() ?: $customer->customer_code }}</span></td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $customer->full_name ?: '-' }}</strong>
                                        <small>{{ $customer->phone ?: 'No phone' }}</small>
                                    </div>
                                </td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($ledgerTotal) }}</td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($receivedTotal) }}</td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($remainingTotal) }}</td>
                                <td>{{ $latestPayment?->methodLabel() ?: '-' }}</td>
                                <td>{{ $latestPayment?->payment_date?->format('d M Y') ?: '-' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View payment" aria-label="View payment" data-bs-toggle="modal" data-bs-target="#paymentCustomerModal-{{ $customer->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-results-cell">{{ $summary['total'] === 0 ? 'No customer payments found.' : 'No matching payments found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $paymentCustomers->firstItem() ?? 0 }} to {{ $paymentCustomers->lastItem() ?? 0 }} of {{ $paymentCustomers->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $paymentCustomers->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($paymentCustomers as $customer)
            @php
                $latestPayment = $customer->latestPaymentRecord();
                $receivedTotal = $customer->receivedPaymentTotal();
                $remainingTotal = $customer->outstandingBalanceTotal();
                $ledgerTotal = $customer->paymentLedgerTotal();
            @endphp
            <div class="modal fade customer-details-modal repair-details-modal payment-details-modal" id="paymentCustomerModal-{{ $customer->id }}" tabindex="-1" aria-labelledby="paymentCustomerModalLabel-{{ $customer->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="customer-modal-header inventory-modal-header">
                            <div class="customer-modal-heading">
                                <span class="repair-modal-code">{{ $latestPayment?->code() ?: '-' }}</span>
                                <h2 id="paymentCustomerModalLabel-{{ $customer->id }}">Payment Details</h2>
                                <p class="repair-modal-customer">{{ $customer->full_name ?: '-' }}</p>
                            </div>
                            <div class="inventory-modal-meta">
                                <div>
                                    <span>Last Payment</span>
                                    <strong>{{ $latestPayment?->payment_date?->format('d M Y') ?: '-' }}</strong>
                                </div>
                            </div>
                            <button type="button" class="customer-modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="modal-body customer-modal-body">
                            <div class="repair-detail-summary payment-modal-summary">
                                <div class="repair-metric-card">
                                    <span>Total</span>
                                    <strong>{{ \App\Helpers\CurrencyHelper::format($ledgerTotal) }}</strong>
                                </div>
                                <div class="repair-metric-card">
                                    <span>Received</span>
                                    <strong>{{ \App\Helpers\CurrencyHelper::format($receivedTotal) }}</strong>
                                </div>
                                <div class="repair-metric-card">
                                    <span>Remaining</span>
                                    <strong>{{ \App\Helpers\CurrencyHelper::format($remainingTotal) }}</strong>
                                </div>
                            </div>

                            <div class="table-scroll repair-payments-table payment-modal-table">
                                <div class="inventory-section-heading">
                                    <span>Payment Invoice Breakdown</span>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Invoice</th>
                                            <th>Type</th>
                                            <th>Received Cash</th>
                                            <th>Pending After</th>
                                            <th>Method</th>
                                            <th>Received Date</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->paymentRecords() as $payment)
                                            @forelse ($payment->allocations as $allocation)
                                                <tr>
                                                    <td><span class="code-text">{{ $payment->code() }}</span></td>
                                                    <td><span class="code-text">{{ $allocation->documentNumber() }}</span></td>
                                                    <td>{{ $allocation->documentTypeLabel() }}</td>
                                                    <td>{{ \App\Helpers\CurrencyHelper::format($allocation->allocated_amount) }}</td>
                                                    <td>
                                                        @if ($allocation->invoice_type === 'repair')
                                                            {{ \App\Helpers\CurrencyHelper::format($allocation->repairJob?->remainingAmount() ?? 0) }}
                                                        @else
                                                            {{ \App\Helpers\CurrencyHelper::format($allocation->invoice?->remaining_amount ?? 0) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $payment->methodLabel() }}</td>
                                                    <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                                    <td>{{ $payment->createdBy?->name ?: '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td><span class="code-text">{{ $payment->code() }}</span></td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>{{ \App\Helpers\CurrencyHelper::format($payment->receivedAmount()) }}</td>
                                                    <td>-</td>
                                                    <td>{{ $payment->methodLabel() }}</td>
                                                    <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                                    <td>{{ $payment->createdBy?->name ?: '-' }}</td>
                                                </tr>
                                            @endforelse
                                        @empty
                                            <tr>
                                                <td colspan="8" class="no-results-cell">No payment details found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="customer-notes-panel sales-modal-notes-panel">
                                <span>Notes</span>
                                <p>{{ $latestPayment?->notes ?: 'No notes added for the latest payment.' }}</p>
                            </div>
                        </div>

                        <div class="customer-modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</x-app-layout>
