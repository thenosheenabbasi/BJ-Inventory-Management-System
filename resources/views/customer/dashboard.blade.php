<x-app-layout>
    <x-slot name="pageTitle">My Dashboard</x-slot>
    <x-slot name="pageBreadcrumb">Home / My Dashboard</x-slot>

    <section class="dashboard-page customer-dashboard-page">
        <div class="kpi-grid customer-kpi-grid">
            <article class="kpi-card">
                <div class="kpi-icon icon-sales" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2Z"/><path d="M8 8h8M8 12h8"/></svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Amount</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($summary['total']) }}</p>
                    <p class="kpi-trend">{{ number_format($summary['invoice_count']) }} total invoices</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-complete" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Amount Paid</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($summary['paid']) }}</p>
                    <p class="kpi-trend text-success">Payment received</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-alert" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/></svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Pending Amount</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($summary['pending']) }}</p>
                    <p class="kpi-trend text-warning">{{ number_format($summary['pending_count']) }} pending invoices</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-repair" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14.7 6.3a4 4 0 0 0-5-5L7 4l3 3-7 7 7 7 7-7-3-3 2.7-2.7Z"/></svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Sale + Repair</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($summary['sale_total']) }}</p>
                    <p class="kpi-trend">{{ \App\Helpers\CurrencyHelper::format($summary['repair_total']) }} repair amount</p>
                </div>
            </article>
        </div>

        <div class="table-card customer-pending-card">
            <div class="table-card-header">
                <div>
                    <h2>Pending Invoices</h2>
                    <p>Invoices that still have an outstanding balance.</p>
                </div>
            </div>
            <div class="table-scroll dashboard-table-scroller">
                <table class="dashboard-pending-table customer-invoice-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingInvoices as $invoice)
                            <tr>
                                <td>{{ $invoice['date']?->format('d M Y') ?: '-' }}</td>
                                <td><span class="code-text">{{ $invoice['number'] }}</span></td>
                                <td>{{ number_format($invoice['quantity']) }}</td>
                                <td>
                                    {{ $invoice['unit_price'] !== null
                                        ? \App\Helpers\CurrencyHelper::format($invoice['unit_price'])
                                        : 'Multiple' }}
                                </td>
                                <td><span class="status-badge {{ $invoice['status_class'] }}">{{ $invoice['status'] }}</span></td>
                                <td>{{ \App\Helpers\CurrencyHelper::format($invoice['total']) }}</td>
                                <td class="text-success">{{ \App\Helpers\CurrencyHelper::format($invoice['paid']) }}</td>
                                <td class="text-warning">{{ \App\Helpers\CurrencyHelper::format($invoice['pending']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="no-results-cell">No pending invoices. Your account is clear.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
