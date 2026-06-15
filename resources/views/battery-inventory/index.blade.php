<x-app-layout>
    <x-slot name="pageTitle">Battery Inventory</x-slot>
    <x-slot name="pageBreadcrumb">Home / Battery Inventory</x-slot>

    <section class="module-page inventory-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Manage laptop batteries, stock levels, prices, and warranty details.</p>
            </div>
            <a href="{{ route('battery-inventory.create') }}" class="btn btn-brand btn-compact">+ Add Battery</a>
        </div>

        <div class="table-card customer-records-card customers-modern-card inventory-modern-card">
            <div class="table-card-header customer-records-header customers-modern-card-header">
                @if ($summary['total'] > 0)
                    <form method="GET" action="{{ route('battery-inventory.index') }}" class="customer-search-form">
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search brand, model, code..." aria-label="Battery search">
                    </form>
                @endif
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table inventory-modern-table">
                    <thead>
                        <tr>
                            <th>Battery Code</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Stock</th>
                            <th>Sale Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batteries as $battery)
                            <tr>
                                <td><span class="code-text">{{ $battery->battery_code }}</span></td>
                                <td>{{ $battery->brand }}</td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $battery->model }}</strong>
                                        <small>{{ $conditions[$battery->condition] ?? $battery->condition }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="inventory-stock-cell">
                                        <strong>{{ $battery->stock_quantity }}</strong>
                                        @if ($battery->isLowStock())
                                            <span class="inventory-low-stock-badge">Low Stock</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ \App\Helpers\CurrencyHelper::format($battery->sale_price) }}</td>
                                <td>
                                    <span class="status-badge customer-status-{{ $battery->status }}">
                                        {{ $statuses[$battery->status] ?? $battery->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View battery" aria-label="View battery" data-bs-toggle="modal" data-bs-target="#batteryDetailsModal-{{ $battery->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <a href="{{ route('battery-inventory.edit', $battery) }}" class="action-btn icon-action" title="Edit battery" aria-label="Edit battery">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        </a>
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('battery-inventory.destroy', $battery) }}" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn icon-action danger" title="Delete battery" aria-label="Delete battery">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="no-results-cell">{{ $summary['total'] === 0 ? 'No batteries found.' : 'No matching batteries found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $batteries->firstItem() ?? 0 }} to {{ $batteries->lastItem() ?? 0 }} of {{ $batteries->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $batteries->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($batteries as $battery)
            <div class="modal fade customer-details-modal inventory-details-modal" id="batteryDetailsModal-{{ $battery->id }}" tabindex="-1" aria-labelledby="batteryDetailsModalLabel-{{ $battery->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="customer-modal-header inventory-modal-header">
                            <div class="inventory-modal-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="7" width="16" height="10" rx="2"/>
                                    <path d="M21 11v2"/>
                                    <path d="M7 11h4"/>
                                </svg>
                            </div>
                            <div class="customer-modal-heading">
                                <h2 id="batteryDetailsModalLabel-{{ $battery->id }}">{{ $battery->brand }} {{ $battery->model }}</h2>
                                <div class="customer-modal-badges">
                                    <span class="status-badge customer-status-{{ $battery->status }}">
                                        {{ $statuses[$battery->status] ?? $battery->status }}
                                    </span>
                                    <span class="customer-type-pill">
                                        {{ $conditions[$battery->condition] ?? $battery->condition }}
                                    </span>
                                    @if ($battery->isLowStock())
                                        <span class="inventory-low-stock-badge">Low Stock</span>
                                    @endif
                                </div>
                            </div>
                            <div class="inventory-modal-meta">
                                <div>
                                    <span>Created Date</span>
                                    <strong>{{ $battery->created_at?->format('d M Y') ?: '-' }}</strong>
                                </div>
                                <div>
                                    <span>Battery Code</span>
                                    <strong>{{ $battery->battery_code }}</strong>
                                </div>
                            </div>
                            <button type="button" class="customer-modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="modal-body customer-modal-body">
                            <div class="inventory-section-heading">
                                <span>Battery Information</span>
                            </div>

                            <div class="customer-detail-grid inventory-detail-grid">
                                <div class="customer-detail-item">
                                    <span>Brand</span>
                                    <strong>{{ $battery->brand }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Model</span>
                                    <strong>{{ $battery->model }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Purchase Price</span>
                                    <strong>{{ \App\Helpers\CurrencyHelper::format($battery->purchase_price) }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Sale Price</span>
                                    <strong>{{ \App\Helpers\CurrencyHelper::format($battery->sale_price) }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Stock</span>
                                    <strong>{{ $battery->stock_quantity }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Low Stock Alert</span>
                                    <strong>{{ $battery->low_stock_alert_quantity }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Warranty</span>
                                    <strong>{{ $battery->warranty_days }} days</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Supplier</span>
                                    <strong>{{ $battery->supplier?->company_name ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created By</span>
                                    <strong>{{ $battery->createdBy?->name ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created Date</span>
                                    <strong>{{ $battery->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Last Updated</span>
                                    <strong>{{ $battery->updated_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                            </div>

                            <div class="customer-notes-panel">
                                <span>Notes</span>
                                <p>{{ $battery->notes ?: 'No notes added for this battery.' }}</p>
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
