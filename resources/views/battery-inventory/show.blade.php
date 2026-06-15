<x-app-layout>
    <x-slot name="pageTitle">Battery Details</x-slot>
    <x-slot name="pageBreadcrumb">Home / Battery Inventory / {{ $battery->battery_code }}</x-slot>

    <section class="module-page">
        <div class="module-header">
            <div>
                <h2>{{ $battery->brand }} {{ $battery->model }}</h2>
                <p>{{ $battery->battery_code }} · {{ \App\Helpers\CurrencyHelper::format($battery->sale_price) }}</p>
            </div>
            <div class="module-actions">
                <a href="{{ route('battery-inventory.index') }}" class="btn btn-light">Back</a>
                <a href="{{ route('battery-inventory.edit', $battery) }}" class="btn btn-brand">Edit Battery</a>
                @if ($canDelete)
                    <form method="POST" action="{{ route('battery-inventory.destroy', $battery) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="profile-grid inventory-profile-grid">
            <div class="table-card profile-main-card">
                <div class="profile-summary">
                    <div class="profile-avatar">{{ strtoupper(substr($battery->brand, 0, 1)) }}</div>
                    <div>
                        <h3>{{ $battery->brand }} {{ $battery->model }}</h3>
                        <p>{{ $battery->battery_code }}</p>
                        <div class="inventory-detail-badges">
                            <span class="status-badge customer-status-{{ $battery->status }}">
                                {{ $statuses[$battery->status] ?? $battery->status }}
                            </span>
                            <span class="type-badge type-both">
                                {{ $conditions[$battery->condition] ?? $battery->condition }}
                            </span>
                            @if ($battery->isLowStock())
                                <span class="inventory-low-stock-badge">Low Stock</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="detail-grid inventory-detail-grid">
                    <div class="detail-item">
                        <span>Battery Code</span>
                        <strong>{{ $battery->battery_code }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Brand</span>
                        <strong>{{ $battery->brand }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Model</span>
                        <strong>{{ $battery->model }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Purchase Price</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($battery->purchase_price) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Sale Price</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($battery->sale_price) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Stock</span>
                        <strong>{{ $battery->stock_quantity }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Low Stock Alert</span>
                        <strong>{{ $battery->low_stock_alert_quantity }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Warranty</span>
                        <strong>{{ $battery->warranty_days }} days</strong>
                    </div>
                    <div class="detail-item">
                        <span>Supplier</span>
                        <strong>{{ $battery->supplier?->company_name ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Created By</span>
                        <strong>{{ $battery->createdBy?->name ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Created Date</span>
                        <strong>{{ $battery->created_at?->format('d M Y') ?: '-' }}</strong>
                    </div>
                </div>

                <div class="notes-box">
                    <span>Notes</span>
                    <p>{{ $battery->notes ?: 'No notes added.' }}</p>
                </div>
            </div>

            <div class="history-grid">
                <div class="table-card history-card">
                    <h3>Stock Watch</h3>
                    <p>{{ $battery->isLowStock() ? 'This item is at or below its low stock alert quantity.' : 'This item is above its low stock alert quantity.' }}</p>
                </div>
                <div class="table-card history-card">
                    <h3>Pricing</h3>
                    <p>Purchase: {{ \App\Helpers\CurrencyHelper::format($battery->purchase_price) }} · Sale: {{ \App\Helpers\CurrencyHelper::format($battery->sale_price) }}</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
