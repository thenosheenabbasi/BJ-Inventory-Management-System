<x-app-layout>
    <x-slot name="pageTitle">Customer Profile</x-slot>
    <x-slot name="pageBreadcrumb">Home / Customers / {{ $customer->customer_code }}</x-slot>

    <section class="module-page">
        <div class="module-header">
            <div>
                <h2>{{ $customer->full_name }}</h2>
                <p>{{ $customer->customer_code }} · {{ $customer->phone }}</p>
            </div>
            <div class="module-actions">
                <a href="{{ route('customers.index') }}" class="btn btn-light">Back</a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-brand">Edit Customer</a>
                @if ($canDelete)
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="profile-grid">
            <div class="table-card profile-main-card">
                <div class="profile-summary">
                    <div class="profile-avatar">{{ strtoupper(substr($customer->full_name, 0, 1)) }}</div>
                    <div>
                        <h3>{{ $customer->full_name }}</h3>
                        <p>{{ $customer->email ?: 'No email added' }}</p>
                        <span class="status-badge {{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span>Customer Code</span>
                        <strong>{{ $customer->customer_code }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Phone</span>
                        <strong>{{ $customer->phone }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>WhatsApp</span>
                        <strong>{{ $customer->whatsapp ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Email</span>
                        <strong>{{ $customer->email ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>City</span>
                        <strong>{{ $customer->city ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Country</span>
                        <strong>{{ $customer->country }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Customer Type</span>
                        <strong>{{ ucwords(str_replace('_', ' ', $customer->customer_type)) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Created By</span>
                        <strong>{{ $customer->createdBy?->name ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Total Paid</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($totalPaid) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Outstanding Balance</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($outstandingBalance) }}</strong>
                    </div>
                </div>

                @if ($customer->notes)
                    <div class="notes-box">
                        <span>Notes</span>
                        <p>{{ $customer->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="history-grid">
                <div class="table-card history-card">
                    <h3>Repair History</h3>
                    <p>Repair jobs will appear here once the repair module is connected.</p>
                </div>
                <div class="table-card history-card">
                    <h3>Purchase History</h3>
                    <div class="table-scroll customer-history-table">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoiceHistory as $invoice)
                                    <tr>
                                        <td><span class="code-text">{{ $invoice->sale_number }}</span></td>
                                        <td>{{ \App\Helpers\CurrencyHelper::format($invoice->total_amount) }}</td>
                                        <td><span class="status-badge {{ $invoice->paymentStatusBadgeClass() }}">{{ $invoice->paymentStatusLabel() }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="no-results-cell">No purchases found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="table-card history-card">
                    <h3>Payment History</h3>
                    <div class="table-scroll customer-history-table">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paymentHistory as $payment)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payments.show', $payment) }}" class="code-text">{{ $payment->code() }}</a>
                                        </td>
                                        <td>{{ \App\Helpers\CurrencyHelper::format($payment->total_payment_amount) }}</td>
                                        <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="no-results-cell">No payments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="table-card history-card">
                    <h3>Invoice History</h3>
                    <div class="table-scroll customer-history-table">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoiceHistory as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('sales.show', $invoice) }}" class="code-text">{{ $invoice->sale_number }}</a>
                                        </td>
                                        <td>{{ \App\Helpers\CurrencyHelper::format($invoice->received_amount) }}</td>
                                        <td>{{ \App\Helpers\CurrencyHelper::format($invoice->remaining_amount) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="no-results-cell">No invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
