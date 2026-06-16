<x-app-layout>
    <x-slot name="pageTitle">Payment Detail</x-slot>
    <x-slot name="pageBreadcrumb">Home / Payments / {{ $payment->code() }}</x-slot>

    <section class="module-page repair-detail-page payment-detail-page">
        <div class="module-header repair-detail-header">
            <div>
                <h2>{{ $payment->code() }}</h2>
                <p>{{ $payment->customer?->full_name ?: '-' }} · {{ $payment->payment_date?->format('d M Y') ?: '-' }}</p>
            </div>
            <div class="module-actions">
                <a href="{{ route('payments.index') }}" class="btn btn-light">Back</a>
                @if ($canCreate)
                    <a href="{{ route('payments.create', ['customer_id' => $payment->customer_id]) }}" class="btn btn-brand">Receive More</a>
                @endif
            </div>
        </div>

        <div class="repair-detail-summary">
            <div class="repair-metric-card">
                <span>Payment Amount</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($payment->total_payment_amount) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Method</span>
                <strong>{{ $payment->methodLabel() }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Date</span>
                <strong>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Created By</span>
                <strong>{{ $payment->createdBy?->name ?: '-' }}</strong>
            </div>
        </div>

        <div class="table-card repair-detail-card">
            <div class="inventory-section-heading">
                <span>Payment Breakdown</span>
            </div>
            <div class="table-scroll repair-payments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-end">Allocated Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->allocations as $allocation)
                            <tr>
                                <td>{{ $allocation->documentTypeLabel() }}</td>
                                <td><span class="code-text">{{ $allocation->documentNumber() }}</span></td>
                                <td>{{ $payment->customer?->full_name ?: '-' }}</td>
                                <td><span class="status-badge {{ $allocation->documentStatusBadgeClass() }}">{{ $allocation->documentStatusLabel() }}</span></td>
                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($allocation->allocated_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card repair-detail-card">
            <div class="inventory-section-heading">
                <span>Notes</span>
            </div>
            <div class="notes-box">
                <p>{{ $payment->notes ?: 'No notes added for this payment.' }}</p>
            </div>
        </div>
    </section>
</x-app-layout>
