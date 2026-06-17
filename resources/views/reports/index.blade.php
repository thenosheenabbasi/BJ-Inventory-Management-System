<x-app-layout>
    <x-slot name="pageTitle">Reports</x-slot>
    <x-slot name="pageBreadcrumb">Home / Reports</x-slot>

    <section class="module-page reports-page">
        <div class="customer-page-header reports-header">
            <div>
                <p>Business performance, collections, stock alerts, and outstanding balances.</p>
            </div>

            <form method="GET" action="{{ route('reports.index') }}" class="reports-filter-form">
                <div class="reports-date-field">
                    <label for="start_date">From</label>
                    <input id="start_date" type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="form-control">
                </div>
                <div class="reports-date-field">
                    <label for="end_date">To</label>
                    <input id="end_date" type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="form-control">
                </div>
                <button type="submit" class="btn btn-brand">Apply</button>
            </form>
        </div>

        <div class="reports-period">
            <span>Report Period</span>
            <strong>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</strong>
        </div>

        <div class="repair-detail-summary reports-summary-grid">
            <div class="repair-metric-card">
                <span>Gross Business</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($summary['gross_business']) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Received</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($summary['gross_received']) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Remaining</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($summary['gross_remaining']) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Collections</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($summary['collection_total']) }}</strong>
            </div>
        </div>

        <div class="reports-breakdown-grid">
            <div class="table-card report-metric-panel">
                <div class="table-card-header">
                    <h2>Sales Report</h2>
                </div>
                <div class="report-lines">
                    <div><span>Invoices</span><strong>{{ number_format($summary['sales_count']) }}</strong></div>
                    <div><span>Total</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['sales_total']) }}</strong></div>
                    <div><span>Received</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['sales_received']) }}</strong></div>
                    <div><span>Remaining</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['sales_remaining']) }}</strong></div>
                </div>
            </div>

            <div class="table-card report-metric-panel">
                <div class="table-card-header">
                    <h2>Repair Battery Report</h2>
                </div>
                <div class="report-lines">
                    <div><span>Jobs</span><strong>{{ number_format($summary['repair_count']) }}</strong></div>
                    <div><span>Total</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['repair_total']) }}</strong></div>
                    <div><span>Paid</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['repair_paid']) }}</strong></div>
                    <div><span>Remaining</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['repair_remaining']) }}</strong></div>
                </div>
            </div>

            <div class="table-card report-metric-panel">
                <div class="table-card-header">
                    <h2>Inventory Report</h2>
                </div>
                <div class="report-lines">
                    <div><span>Stock Qty</span><strong>{{ number_format($summary['inventory_stock']) }}</strong></div>
                    <div><span>Stock Value</span><strong>{{ \App\Helpers\CurrencyHelper::format($summary['inventory_value']) }}</strong></div>
                    <div><span>Low Stock</span><strong>{{ number_format($summary['low_stock_count']) }}</strong></div>
                    <div><span>Payments</span><strong>{{ number_format($summary['collection_count']) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="reports-table-grid">
            <div class="table-card">
                <div class="table-card-header">
                    <h2>Top Selling Batteries</h2>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Battery</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topSellingBatteries as $item)
                                <tr>
                                    <td>
                                        <span class="code-text">{{ $item->battery?->battery_code ?: '-' }}</span>
                                        <div>{{ trim(($item->battery?->brand ?: '').' '.($item->battery?->model ?: '')) ?: 'Battery removed' }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format($item->quantity_sold) }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($item->sales_total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="no-results-cell">No sales found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h2>Recent Collections</h2>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCollections as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('d M Y') ?: $payment->created_at?->format('d M Y') }}</td>
                                    <td><span class="code-text">{{ $payment->code() }}</span></td>
                                    <td>{{ $payment->customer?->full_name ?: '-' }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($payment->receivedAmount()) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="no-results-cell">No collections found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h2>Outstanding Customers</h2>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($outstandingCustomers as $row)
                                <tr>
                                    <td>
                                        <span class="code-text">{{ $row['customer']->customer_code }}</span>
                                        <div>{{ $row['customer']->full_name ?: '-' }}</div>
                                    </td>
                                    <td>{{ $row['customer']->phone ?: '-' }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($row['outstanding']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="no-results-cell">No outstanding balances.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h2>Low Stock Batteries</h2>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Battery</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Alert</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockBatteries as $battery)
                                <tr>
                                    <td>
                                        <span class="code-text">{{ $battery->battery_code }}</span>
                                        <div>{{ $battery->brand }} {{ $battery->model }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format($battery->stock_quantity) }}</td>
                                    <td class="text-end">{{ number_format($battery->low_stock_alert_quantity) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="no-results-cell">No low stock batteries.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
