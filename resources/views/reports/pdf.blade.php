<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Summary Report</title>
    <style>
        @page { margin: 22px 24px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #101828; font-family: DejaVu Sans, sans-serif; font-size: 8px; background: #fff; }
        .header { width: 100%; border-bottom: 2px solid #272757; border-collapse: collapse; background: #fff; }
        .header td { padding: 12px 14px; vertical-align: top; }
        .company { margin: 0 0 3px; color: #272757; font-size: 15px; font-weight: bold; }
        h1 { margin: 0 0 3px; color: #101828; font-size: 12px; }
        .muted { color: #667085; }
        .header-values { text-align: right; }
        .header-values strong, .header-values span { display: block; }
        .header-values strong { color: #272757; font-size: 10px; }
        .header-values span { margin-top: 3px; color: #667085; font-size: 8px; }
        .received, .pending { color: #272757 !important; }
        .section { margin-top: 11px; border: 1px solid #d8ddea; page-break-inside: auto; }
        .section-title { width: 100%; padding: 6px 8px; border-bottom: 1px solid #272757; background: #272757; color: #fff; font-weight: bold; }
        .section-title td:last-child { color: #fff; text-align: right; font-size: 7px; font-weight: normal; }
        .financial-summary { border: 1px solid #cfd6e4; }
        .financial-summary .section-title { padding: 7px 9px; background: #272757; color: #fff; }
        .financial-summary .section-title td:last-child { color: #fff; }
        .financial-title { display: block; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .financial-subtitle { display: block; margin-top: 2px; color: #d9dbea; font-size: 7px; font-weight: normal; }
        .financial-totals { width: 100%; border-collapse: collapse; background: #fff; }
        .financial-totals td { width: 33.33%; padding: 10px 12px; border-right: 1px solid #d0d5dd; }
        .financial-totals td:last-child { border-right: 0; }
        .financial-totals td:nth-child(1),
        .financial-totals td:nth-child(2),
        .financial-totals td:nth-child(3) { background: #fff; }
        .financial-totals span, .financial-totals strong { display: block; }
        .financial-totals span { color: #667085; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .financial-totals strong { margin-top: 4px; color: #101828; font-size: 13px; }
        .financial-summary .data th { background: #fff; color: #344054; }
        .financial-summary .data tbody tr:nth-child(1) td,
        .financial-summary .data tbody tr:nth-child(2) td { background: #fff; }
        .financial-summary .data tbody td:first-child { color: #272757; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        .data th { padding: 5px 6px; border: 1px solid #d8ddea; background: #fff; color: #344054; font-size: 7px; text-align: left; text-transform: uppercase; }
        .data td { padding: 5px 6px; border: 1px solid #d8ddea; vertical-align: top; }
        .data tbody tr:nth-child(even) td { background: #fff; }
        .data tfoot td { background: #fff; color: #272757; font-weight: bold; }
        .sale-section,
        .repair-section,
        .payment-section { border-left: 5px solid #272757; }
        .sale-section .section-title,
        .repair-section .section-title,
        .payment-section .section-title { background: #e8e9ed; color: #272757; }
        .sale-section .section-title td:last-child,
        .repair-section .section-title td:last-child,
        .payment-section .section-title td:last-child { color: #667085; }
        .sale-section .data th,
        .repair-section .data th,
        .payment-section .data th { background: #fff; }
        .right { text-align: right !important; }
        .code { color: #272757; font-weight: bold; }
        .empty { padding: 12px !important; color: #667085; text-align: center; }
        .signatures { width: 100%; margin-top: 34px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures td { width: 50%; padding: 0; text-align: center; vertical-align: bottom; }
        .signatures td:first-child { padding-right: 55px; }
        .signatures td:last-child { padding-left: 55px; }
        .signature-space { height: 34px; border-bottom: 1px solid #344054; }
        .signature-label { padding-top: 5px; color: #475467; font-size: 8px; font-weight: bold; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <p class="company">M. Bilal jamshed</p>
                <h1>Payment Summary Report</h1>
                <span class="muted">Inventory Management System</span>
            </td>
            <td class="header-values">
                <strong>{{ $selectedCustomer?->full_name ?: 'All Customers' }}</strong>
                <span>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</span>
                <span>{{ $generatedAt->format('d M Y, h:i A') }}</span>
            </td>
        </tr>
    </table>

    <div class="section financial-summary">
        <table class="section-title">
            <tr>
                <td>
                    <span class="financial-title">Financial Summary</span>
                    <span class="financial-subtitle">Payment position for the selected report</span>
                </td>
                <td>{{ number_format($summary['sales_count'] + $summary['repair_count']) }} invoices</td>
            </tr>
        </table>
        <table class="financial-totals">
            <tr>
                <td>
                    <span>Total Invoiced</span>
                    <strong>{{ \App\Helpers\CurrencyHelper::format($summary['gross_business']) }}</strong>
                </td>
                <td>
                    <span>Total Received</span>
                    <strong class="received">{{ \App\Helpers\CurrencyHelper::format($summary['gross_received']) }}</strong>
                </td>
                <td>
                    <span>Outstanding Balance</span>
                    <strong class="pending">{{ \App\Helpers\CurrencyHelper::format($summary['gross_remaining']) }}</strong>
                </td>
            </tr>
        </table>
        <table class="data">
            <thead><tr><th>Category</th><th class="right">Invoices</th><th class="right">Invoiced</th><th class="right">Received</th><th class="right">Outstanding</th></tr></thead>
            <tbody>
                <tr><td><strong>Sale Battery</strong></td><td class="right">{{ number_format($summary['sales_count']) }}</td><td class="right">{{ \App\Helpers\CurrencyHelper::format($summary['sales_total']) }}</td><td class="right received">{{ \App\Helpers\CurrencyHelper::format($summary['sales_received']) }}</td><td class="right pending">{{ \App\Helpers\CurrencyHelper::format($summary['sales_remaining']) }}</td></tr>
                <tr><td><strong>Repair Battery</strong></td><td class="right">{{ number_format($summary['repair_count']) }}</td><td class="right">{{ \App\Helpers\CurrencyHelper::format($summary['repair_total']) }}</td><td class="right received">{{ \App\Helpers\CurrencyHelper::format($summary['repair_paid']) }}</td><td class="right pending">{{ \App\Helpers\CurrencyHelper::format($summary['repair_remaining']) }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section sale-section">
        <table class="section-title"><tr><td>Sale Battery Invoices</td><td>{{ number_format($saleDetails->count()) }} records</td></tr></table>
        <table class="data">
            <thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Battery Details</th><th class="right">Qty</th><th class="right">Total</th><th class="right">Received</th><th class="right">Remaining</th></tr></thead>
            <tbody>
                @forelse ($saleDetails as $sale)
                    <tr>
                        <td>{{ $sale->created_at?->format('d M Y') ?: '-' }}</td>
                        <td class="code">{{ $sale->sale_number }}</td>
                        <td>{{ $sale->customer?->full_name ?: '-' }}</td>
                        <td>@forelse ($sale->items as $item)<div>{{ trim(($item->battery?->brand ?: '').' '.($item->battery?->model ?: '')) ?: 'Battery removed' }}</div>@empty-@endforelse</td>
                        <td class="right">@forelse ($sale->items as $item)<div>{{ number_format($item->quantity) }}</div>@empty-@endforelse</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($sale->received_amount) }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($sale->remaining_amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No sale battery records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section repair-section">
        <table class="section-title"><tr><td>Repair Battery Invoices</td><td>{{ number_format($repairDetails->count()) }} records</td></tr></table>
        <table class="data">
            <thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Battery Details</th><th class="right">Qty</th><th class="right">Total</th><th class="right">Received</th><th class="right">Remaining</th></tr></thead>
            <tbody>
                @forelse ($repairDetails as $repair)
                    <tr>
                        <td>{{ $repair->created_at?->format('d M Y') ?: '-' }}</td>
                        <td class="code">{{ $repair->repair_number }}</td>
                        <td>{{ $repair->customer?->full_name ?: '-' }}</td>
                        <td>{{ $repair->battery_details ?: '-' }}</td>
                        <td class="right">{{ number_format($repair->quantity ?: 1) }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($repair->estimated_cost) }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($repair->paidAmount()) }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($repair->remainingAmount()) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No repair battery records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section payment-section">
        <table class="section-title"><tr><td>Payment Received Record</td><td>{{ number_format($paymentDetails->count()) }} entries</td></tr></table>
        <table class="data">
            <thead><tr><th>Received Date</th><th>Receipt</th><th>Customer</th><th>Invoices Paid</th><th>Method</th><th class="right">Amount Received</th></tr></thead>
            <tbody>
                @forelse ($paymentDetails as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('d M Y') ?: $payment->created_at?->format('d M Y') }}</td>
                        <td class="code">{{ $payment->code() }}</td>
                        <td>{{ $payment->customer?->full_name ?: '-' }}</td>
                        <td>@forelse ($payment->allocations as $allocation)<div>{{ $allocation->documentNumber() }} - {{ \App\Helpers\CurrencyHelper::format($allocation->allocated_amount) }}</div>@empty-@endforelse</td>
                        <td>{{ $payment->methodLabel() }}</td>
                        <td class="right">{{ \App\Helpers\CurrencyHelper::format($payment->receivedAmount()) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No payment records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-space"></div>
                <div class="signature-label">Prepared By</div>
            </td>
            <td>
                <div class="signature-space"></div>
                <div class="signature-label">Authorized Signature</div>
            </td>
        </tr>
    </table>

</body>
</html>
