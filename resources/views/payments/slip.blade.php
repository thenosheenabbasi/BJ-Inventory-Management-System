@php
    $customerName = $customer->full_name ?: 'Customer';
    $brandName = 'M. Bilal jamshed';
    $slipTitle = $customerName.' Payment Statement';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $slipTitle }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #667085;
            --line: #d0d5dd;
            --soft: #f8fafc;
            --brand: #272757;
            --success: #0f766e;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .toolbar {
            width: 210mm;
            margin: 18px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            color: var(--brand);
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--brand);
            color: #ffffff;
            border-color: var(--brand);
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 10mm;
            background: #ffffff;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.12);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .slip {
            min-height: calc(297mm - 20mm);
            border: 1px solid var(--line);
            border-left: 8px solid var(--brand);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .slip-header,
        .slip-body {
            position: relative;
            z-index: 1;
        }

        .slip-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 24px 18px 16px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.9);
        }

        .brand h1 {
            margin: 0 0 6px;
            font-size: 26px;
            line-height: 1;
            letter-spacing: 0;
        }

        .brand p,
        .meta p {
            margin: 0;
            color: var(--muted);
        }

        .brand .statement-label {
            display: block;
            margin-top: 3px;
            color: var(--brand);
            font-weight: 800;
        }

        .meta {
            text-align: right;
            font-size: 12px;
        }

        .meta strong {
            display: block;
            margin: 2px 0 4px;
            color: var(--ink);
            font-size: 15px;
        }

        .meta .statement-code {
            margin-bottom: 0;
            color: var(--brand);
            font-weight: 800;
        }

        .slip-body {
            padding: 22px 18px 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .section {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.88);
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
        }

        .plain-section {
            border: 0;
            border-radius: 0;
            background: transparent;
            overflow: visible;
        }

        .section-title {
            padding: 11px 14px;
            background: var(--soft);
            border-bottom: 1px solid var(--line);
            color: var(--brand);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .plain-section .section-title {
            padding: 0 0 10px;
            background: transparent;
            border-bottom: 1px solid var(--line);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding-top: 12px;
        }

        .info span,
        .summary-card span {
            display: block;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info strong,
        .summary-card strong {
            display: block;
            margin-top: 4px;
            color: var(--ink);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .block {
            margin-top: 18px;
        }

        .statement-totals {
            width: 315px;
            margin: 14px 0 0 auto;
            border: 1px solid var(--line);
            border-radius: 7px;
            overflow: hidden;
            background: #ffffff;
        }

        .statement-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 34px;
            padding: 7px 12px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
            font-size: 12px;
        }

        .statement-total-row:last-child {
            border-bottom: 0;
        }

        .statement-total-row span {
            display: inline;
            color: var(--ink);
            font-size: 12px;
            font-weight: 400;
            text-transform: none;
            line-height: 1.2;
        }

        .statement-total-row strong {
            display: inline;
            margin-top: 0;
            color: var(--ink);
            font-size: 13px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .statement-total-row.received strong {
            color: var(--success);
        }

        .statement-total-row.remaining {
            color: #ffffff;
            font-weight: 800;
            background: var(--brand);
        }

        .statement-total-row.remaining span {
            color: #ffffff;
            font-weight: 800;
        }

        .statement-total-row.remaining strong {
            color: #ffffff;
            font-weight: 800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.92);
        }

        th,
        td {
            padding: 9px 10px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f6fa;
            color: var(--brand);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) td {
            background: rgba(248, 250, 252, 0.62);
        }

        td small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            line-height: 1.35;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .invoice-total {
            text-align: right;
            vertical-align: middle;
        }

        .invoice-record-table th {
            text-align: center;
        }

        .invoice-record-table th:first-child,
        .invoice-record-table td:first-child {
            text-align: left;
        }

        .invoice-record-table th:nth-child(2),
        .invoice-record-table td:nth-child(2) {
            text-align: center;
        }

        .invoice-record-table th:nth-child(3),
        .invoice-record-table td:nth-child(3) {
            text-align: left;
        }

        .invoice-record-table th:nth-child(n+4),
        .invoice-record-table td:nth-child(n+4) {
            text-align: center;
        }

        .invoice-record-table th:last-child,
        .invoice-record-table td:last-child {
            text-align: right;
        }

        .invoice-lines {
            display: grid;
            gap: 3px;
        }

        .invoice-line {
            min-height: 18px;
            line-height: 1.35;
        }

        .code {
            color: #111a56;
            font-weight: 800;
            white-space: nowrap;
        }

        .payment-lines {
            display: grid;
            gap: 4px;
        }

        .payment-line {
            min-height: 20px;
            color: var(--ink);
            font-size: 11px;
            line-height: 1.35;
        }

        .payment-line.amount {
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
        }

        .payment-line.code {
            font-weight: 600;
        }

        .payment-record-table th,
        .payment-record-table td {
            text-align: left;
        }

        .payment-record-table th.number,
        .payment-record-table td.number {
            text-align: right;
        }

        .payment-record-table .payment-line.amount {
            text-align: right;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 38px;
            margin-top: auto;
            padding-top: 22px;
        }

        .signature {
            width: 76%;
            margin: 0 auto;
            padding-top: 7px;
            border-top: 1px solid var(--ink);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        .footer-note {
            margin-top: 10px;
            padding: 8px 14px;
            border-top: 4px solid var(--brand);
            border-radius: 8px;
            background: var(--brand);
            color: #ffffff;
            font-size: 11px;
            line-height: 1.45;
            text-align: center;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media (max-width: 760px) {
            .toolbar,
            .page {
                width: 100%;
            }

            .slip-header,
            .info-grid {
                grid-template-columns: 1fr;
            }

            .statement-totals {
                width: 100%;
            }

            .meta {
                text-align: left;
            }
        }

        @media print {
            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .page {
                width: 100%;
                min-height: calc(297mm - 20mm);
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .slip-header {
                grid-template-columns: 1fr auto;
            }

            .meta {
                text-align: right;
            }

            .info-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .statement-totals {
                width: 315px;
                margin-left: auto;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('payments.index') }}" class="btn">Back</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="page">
        <section class="slip">
            <header class="slip-header">
                <div class="brand">
                    <h1>{{ $brandName }}</h1>
                    <p>
                        Inventory Management System
                        <span class="statement-label">Payment Statement</span>
                    </p>
                </div>
                <div class="meta">
                    <p class="statement-code">{{ $customer->customer_code ?: '-' }}</p>
                    <strong>{{ $customerName }}</strong>
                    <p>{{ now()->format('d M Y, h:i A') }}</p>
                </div>
            </header>

            <div class="slip-body">
                <div class="section block">
                    <div class="section-title">Invoice Record</div>
                    <table class="invoice-record-table">
                        <thead>
                            <tr>
                                <th>Invoice Date</th>
                                <th>Invoice</th>
                                <th>Details</th>
                                <th class="number">Qty</th>
                                <th class="number">Unit Price</th>
                                <th class="invoice-total">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoiceRows as $invoice)
                                <tr>
                                    <td>{{ $invoice['date']?->format('d M Y') ?: '-' }}</td>
                                    <td><span class="code">{{ $invoice['number'] }}</span></td>
                                    <td>
                                        <div class="invoice-lines">
                                            @foreach ($invoice['details'] as $detail)
                                                <div class="invoice-line">{{ $detail }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="number">
                                        <div class="invoice-lines">
                                            @foreach ($invoice['quantities'] as $quantity)
                                                <div class="invoice-line">{{ number_format($quantity) }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="number">
                                        <div class="invoice-lines">
                                            @foreach ($invoice['unit_prices'] as $unitPrice)
                                                <div class="invoice-line">{{ \App\Helpers\CurrencyHelper::format($unitPrice) }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="invoice-total">{{ \App\Helpers\CurrencyHelper::format($invoice['total']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="section block">
                    <div class="section-title">Payment Received Record</div>
                    <table class="payment-record-table">
                        <thead>
                            <tr>
                                <th>Received Date</th>
                                <th>Receipt</th>
                                <th>Invoices</th>
                                <th class="number">Received Amount</th>
                                <th>Method</th>
                                <th class="number">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                    <td><span class="code">{{ $payment->code() }}</span></td>
                                    <td>
                                        <div class="payment-lines">
                                            @forelse ($payment->allocations as $allocation)
                                                <div class="payment-line code">{{ $allocation->documentNumber() }}</div>
                                            @empty
                                                <div class="payment-line">No allocation</div>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="number">
                                        <div class="payment-lines">
                                            @forelse ($payment->allocations as $allocation)
                                                <div class="payment-line amount">{{ \App\Helpers\CurrencyHelper::format($allocation->allocated_amount) }}</div>
                                            @empty
                                                <div class="payment-line amount">-</div>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>{{ $payment->methodLabel() }}</td>
                                    <td class="number"><strong>{{ \App\Helpers\CurrencyHelper::format($payment->receivedAmount()) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No payments received.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="statement-totals">
                    <div class="statement-total-row">
                        <span>Total Invoices</span>
                        <strong>{{ number_format($summary['invoice_count']) }}</strong>
                    </div>
                    <div class="statement-total-row">
                        <span>Total Amount</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($summary['invoice_total']) }}</strong>
                    </div>
                    <div class="statement-total-row received">
                        <span>Received</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($summary['received_total']) }}</strong>
                    </div>
                    <div class="statement-total-row remaining">
                        <span>Remaining</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($summary['remaining_total']) }}</strong>
                    </div>
                </div>

                <div class="signature-grid">
                    <div class="signature">Client Signature</div>
                    <div class="signature">Authorized Signature</div>
                </div>

                <div class="footer-note">
                    Thank you for choosing {{ $brandName }}. Keep this payment statement for invoice and balance verification.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
