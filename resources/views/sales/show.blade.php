<x-app-layout>
    <x-slot name="pageTitle">Sale Details</x-slot>
    <x-slot name="pageBreadcrumb">Home / Sales / {{ $sale->sale_number }}</x-slot>

    <section class="module-page repair-detail-page sales-detail-page">
        <div class="module-header repair-detail-header">
            <div>
                <h2>{{ $sale->sale_number }}</h2>
                <p>{{ $sale->customer?->full_name ?: '-' }} · {{ $sale->paymentStatusLabel() }}</p>
            </div>
            <div class="module-actions">
                <a href="{{ route('sales.index') }}" class="btn btn-light">Back</a>
                <a href="{{ route('sales.slip', $sale) }}" class="btn btn-light" target="_blank">Print Slip</a>
                @if ($canManage)
                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-brand">Edit Sale</a>
                @endif
                @if ($canDelete)
                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="repair-detail-summary sales-detail-summary">
            <div class="repair-metric-card">
                <span>Subtotal</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->subtotal) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Discount</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->discount) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>VAT</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->vat) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Grand Total</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Payment Status</span>
                <strong><span class="status-badge {{ $sale->paymentStatusBadgeClass() }}">{{ $sale->paymentStatusLabel() }}</span></strong>
            </div>
        </div>

        <div class="sales-detail-grid">
            <div class="table-card repair-detail-card">
                <div class="inventory-section-heading">
                    <span>Sale Information</span>
                </div>
                <div class="detail-grid repair-overview-grid">
                    <div class="detail-item">
                        <span>Sale Number</span>
                        <strong>{{ $sale->sale_number }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Created Date</span>
                        <strong>{{ $sale->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Created By</span>
                        <strong>{{ $sale->createdBy?->name ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Items</span>
                        <strong>{{ number_format($sale->items->count()) }}</strong>
                    </div>
                </div>
            </div>

            <div class="table-card repair-detail-card">
                <div class="inventory-section-heading">
                    <span>Customer Information</span>
                </div>
                <div class="profile-summary repair-customer-summary">
                    <div class="profile-avatar">{{ strtoupper(substr($sale->customer?->full_name ?: 'C', 0, 1)) }}</div>
                    <div>
                        <h3>{{ $sale->customer?->full_name ?: '-' }}</h3>
                        <p>{{ $sale->customer?->email ?: 'No email added' }}</p>
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span>Customer Code</span>
                        <strong>{{ $sale->customer?->customer_code ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Phone</span>
                        <strong>{{ $sale->customer?->phone ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>WhatsApp</span>
                        <strong>{{ $sale->customer?->whatsapp ?: '-' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>City</span>
                        <strong>{{ $sale->customer?->city ?: '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card repair-detail-card sales-items-card">
            <div class="inventory-section-heading">
                <span>Items</span>
            </div>
            <div class="table-scroll repair-payments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Battery</th>
                            <th>Quantity</th>
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

        <div class="table-card repair-detail-card sales-items-card">
            <div class="inventory-section-heading">
                <span>Inventory Movement</span>
            </div>
            <div class="table-scroll repair-payments-table">
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

        <div class="table-card repair-detail-card">
            <div class="inventory-section-heading">
                <span>Notes</span>
            </div>
            <div class="notes-box">
                <p>{{ $sale->notes ?: 'No notes added for this sale.' }}</p>
            </div>
        </div>
    </section>
</x-app-layout>
