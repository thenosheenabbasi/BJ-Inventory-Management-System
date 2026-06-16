<x-app-layout>
    <x-slot name="pageTitle">Sales</x-slot>
    <x-slot name="pageBreadcrumb">Home / Sales</x-slot>

    <section class="module-page customers-modern-page sales-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Manage battery sales and customer purchases.</p>
            </div>
            @if ($canManage)
                <a href="{{ route('sales.create') }}" class="btn btn-brand btn-compact">+ Create Sale</a>
            @endif
        </div>

        <div class="table-card customer-records-card customers-modern-card sales-modern-card">
            <div class="table-card-header customer-records-header customers-modern-card-header">
                @if ($summary['total'] > 0)
                    <form method="GET" action="{{ route('sales.index') }}" class="customer-search-form">
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search sales..." aria-label="Sales search">
                    </form>
                @endif
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table sales-modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td><span class="code-text">{{ $sale->sale_number }}</span></td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $sale->customer?->full_name ?: '-' }}</strong>
                                        <small>{{ $sale->customer?->phone ?: 'No phone' }}</small>
                                    </div>
                                </td>
                                <td>{{ number_format($sale->items_count) }}</td>
                                <td>{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</td>
                                <td>
                                    <span class="status-badge {{ $sale->paymentStatusBadgeClass() }}">
                                        {{ $sale->paymentStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $sale->created_at?->format('d M Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View sale" aria-label="View sale" data-bs-toggle="modal" data-bs-target="#saleDetailsModal-{{ $sale->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <a href="{{ route('sales.slip', $sale) }}" class="action-btn icon-action" title="Print sale slip" aria-label="Print sale slip" target="_blank">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/><path d="M18 12h.01"/></svg>
                                        </a>
                                        @if ($canManage)
                                            <a href="{{ route('sales.edit', $sale) }}" class="action-btn icon-action" title="Edit sale" aria-label="Edit sale">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                            </a>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn icon-action danger" title="Delete sale" aria-label="Delete sale">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="no-results-cell">{{ $summary['total'] === 0 ? 'No sales found.' : 'No matching sales found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $sales->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($sales as $sale)
            <div class="modal fade customer-details-modal repair-details-modal sales-details-modal" id="saleDetailsModal-{{ $sale->id }}" tabindex="-1" aria-labelledby="saleDetailsModalLabel-{{ $sale->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="customer-modal-header sales-modal-header">
                            <div class="sales-modal-header-title">
                                <h2 id="saleDetailsModalLabel-{{ $sale->id }}">{{ $sale->sale_number }}</h2>
                            </div>
                            <div class="sales-modal-header-summary">
                                <div>
                                    <span>Created</span>
                                    <strong>{{ $sale->created_at?->format('d M Y') ?: '-' }}</strong>
                                </div>
                            </div>
                            <button type="button" class="customer-modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="modal-body customer-modal-body">
                            <div class="sales-modal-tabs" role="tablist" aria-label="Sale detail sections">
                                <button type="button" class="sales-modal-tab active" id="saleOverviewTab-{{ $sale->id }}" data-bs-toggle="tab" data-bs-target="#saleOverviewPane-{{ $sale->id }}" role="tab" aria-controls="saleOverviewPane-{{ $sale->id }}" aria-selected="true">Overview</button>
                                <button type="button" class="sales-modal-tab" id="saleItemsTab-{{ $sale->id }}" data-bs-toggle="tab" data-bs-target="#saleItemsPane-{{ $sale->id }}" role="tab" aria-controls="saleItemsPane-{{ $sale->id }}" aria-selected="false">Items</button>
                                <button type="button" class="sales-modal-tab" id="saleInventoryTab-{{ $sale->id }}" data-bs-toggle="tab" data-bs-target="#saleInventoryPane-{{ $sale->id }}" role="tab" aria-controls="saleInventoryPane-{{ $sale->id }}" aria-selected="false">Inventory</button>
                                <button type="button" class="sales-modal-tab" id="saleNotesTab-{{ $sale->id }}" data-bs-toggle="tab" data-bs-target="#saleNotesPane-{{ $sale->id }}" role="tab" aria-controls="saleNotesPane-{{ $sale->id }}" aria-selected="false">Notes</button>
                            </div>

                            <div class="tab-content sales-modal-tab-content">
                                <div class="tab-pane fade show active" id="saleOverviewPane-{{ $sale->id }}" role="tabpanel" aria-labelledby="saleOverviewTab-{{ $sale->id }}" tabindex="0">
                                    <div class="sales-overview-layout">
                                        <div class="sales-overview-details">
                                            <section class="sales-overview-section">
                                                <h3>Sale Information</h3>
                                                <div class="sales-overview-rows">
                                                    <div>
                                                        <span>Sale Number</span>
                                                        <strong>{{ $sale->sale_number }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>Payment Status</span>
                                                        <strong>{{ $sale->paymentStatusLabel() }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>Created Date</span>
                                                        <strong>{{ $sale->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>Created By</span>
                                                        <strong>{{ $sale->createdBy?->name ?: '-' }}</strong>
                                                    </div>
                                                </div>
                                            </section>

                                            <section class="sales-overview-section">
                                                <h3>Customer Information</h3>
                                                <div class="sales-overview-rows">
                                                    <div>
                                                        <span>Customer</span>
                                                        <strong>{{ $sale->customer?->full_name ?: '-' }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>Customer Code</span>
                                                        <strong>{{ $sale->customer?->customer_code ?: '-' }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>Phone</span>
                                                        <strong>{{ $sale->customer?->phone ?: '-' }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>WhatsApp</span>
                                                        <strong>{{ $sale->customer?->whatsapp ?: '-' }}</strong>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>

                                        <aside class="sales-overview-amount">
                                            <h3>Amount Breakdown</h3>
                                            <div class="sales-overview-amount-row">
                                                <span>Subtotal</span>
                                                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->subtotal) }}</strong>
                                            </div>
                                            <div class="sales-overview-amount-row">
                                                <span>Discount</span>
                                                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->discount) }}</strong>
                                            </div>
                                            <div class="sales-overview-amount-row">
                                                <span>VAT</span>
                                                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->vat) }}</strong>
                                            </div>
                                            <div class="sales-overview-grand-total">
                                                <span>Grand Total</span>
                                                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</strong>
                                            </div>
                                        </aside>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="saleItemsPane-{{ $sale->id }}" role="tabpanel" aria-labelledby="saleItemsTab-{{ $sale->id }}" tabindex="0">
                                    <div class="table-scroll repair-payments-table sales-modal-table">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Battery</th>
                                                    <th>Qty</th>
                                                    <th>Unit Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($sale->items as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="customer-name">
                                                                <strong>{{ $item->battery?->brand }} {{ $item->battery?->model }}</strong>
                                                                <small>{{ $item->battery?->battery_code ?: 'Battery removed' }}</small>
                                                            </div>
                                                        </td>
                                                        <td>{{ number_format($item->quantity) }}</td>
                                                        <td>{{ \App\Helpers\CurrencyHelper::format($item->unit_price) }}</td>
                                                        <td>{{ \App\Helpers\CurrencyHelper::format($item->total_price) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="saleInventoryPane-{{ $sale->id }}" role="tabpanel" aria-labelledby="saleInventoryTab-{{ $sale->id }}" tabindex="0">
                                    <div class="table-scroll repair-payments-table sales-modal-table">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Battery</th>
                                                    <th>Stock Deducted</th>
                                                    <th>Current Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($sale->items as $item)
                                                    <tr>
                                                        <td>{{ $item->battery?->brand }} {{ $item->battery?->model }} {{ $item->battery?->battery_code ? '(' . $item->battery->battery_code . ')' : '' }}</td>
                                                        <td>{{ number_format($item->quantity) }}</td>
                                                        <td>{{ $item->battery ? number_format($item->battery->stock_quantity) : '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="saleNotesPane-{{ $sale->id }}" role="tabpanel" aria-labelledby="saleNotesTab-{{ $sale->id }}" tabindex="0">
                                    <div class="customer-notes-panel sales-modal-notes-panel">
                                        <span>Notes</span>
                                        <p>{{ $sale->notes ?: 'No notes added for this sale.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="customer-modal-footer">
                            <a href="{{ route('sales.slip', $sale) }}" class="btn btn-light" target="_blank">Print Slip</a>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</x-app-layout>
