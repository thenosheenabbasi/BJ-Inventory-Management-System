@php
    $customerName = $sale->customer?->full_name ?: 'Customer';
    $brandName = 'M. Bilal jamshed';
    $slipTitle = $customerName.' '.$sale->sale_number;
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
            --accent: #272757;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
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

        .slip::before {
            content: "M. Bilal Jamshed";
            position: absolute;
            top: 55%;
            left: 50%;
            width: 100%;
            text-align: center;
            color: rgba(39, 39, 87, 0.08);
            font-size: 54px;
            font-weight: 800;
            text-transform: uppercase;
            transform: translate(-50%, -50%) rotate(-8deg);
            pointer-events: none;
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
            background: rgba(255, 255, 255, 0.88);
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

        .meta {
            text-align: right;
            font-size: 12px;
        }

        .meta strong {
            display: block;
            margin: 5px 0;
            color: var(--ink);
            font-size: 15px;
        }

        .slip-body {
            padding: 26px 18px 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .section {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.86);
            overflow: hidden;
        }

        .section-title {
            padding: 9px 12px;
            background: var(--soft);
            border-bottom: 1px solid var(--line);
            color: var(--brand);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
        }

        .info {
            min-height: 54px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
        }

        .info:nth-child(odd) {
            border-right: 1px solid var(--line);
        }

        .info-full {
            grid-column: 1 / -1;
            border-right: 0;
        }

        .info span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info strong {
            display: block;
            margin-top: 4px;
            color: var(--ink);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .block {
            margin-top: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--soft);
            color: var(--brand);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        td small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .totals {
            width: 310px;
            margin-left: auto;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
        }

        .total-row:last-child {
            border-bottom: 0;
        }

        .grand-total {
            background: var(--brand);
            color: #ffffff;
            font-weight: 800;
        }

        .note {
            min-height: 70px;
            padding: 12px;
            line-height: 1.55;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 64px;
            margin-top: auto;
            padding-top: 64px;
        }

        .signature {
            padding-top: 14px;
            border-top: 1px solid var(--ink);
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .footer-note {
            margin-top: 24px;
            padding: 12px 16px;
            border-top: 4px solid var(--accent);
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

            .top-grid,
            .info-grid {
                grid-template-columns: 1fr;
            }

            .info:nth-child(odd) {
                border-right: 0;
            }

            .totals {
                width: 100%;
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

            .top-grid {
                grid-template-columns: 1fr 1fr;
            }

            .info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('sales.show', $sale) }}" class="btn">Back</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="page">
        <section class="slip">
            <header class="slip-header">
                <div class="brand">
                    <h1>{{ $brandName }}</h1>
                    <p>Inventory Management System</p>
                    <p>Battery Sale Slip</p>
                </div>
                <div class="meta">
                    <p>Sale No</p>
                    <strong>{{ $sale->sale_number }}</strong>
                    <p>{{ now()->format('d M Y, h:i A') }}</p>
                </div>
            </header>

            <div class="slip-body">
                <div class="top-grid">
                    <div class="section">
                        <div class="section-title">Customer Information</div>
                        <div class="info-grid">
                            <div class="info">
                                <span>Customer</span>
                                <strong>{{ $customerName }}</strong>
                            </div>
                            <div class="info">
                                <span>Customer Code</span>
                                <strong>{{ $sale->customer?->customer_code ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Phone</span>
                                <strong>{{ $sale->customer?->phone ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>WhatsApp</span>
                                <strong>{{ $sale->customer?->whatsapp ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Sale Information</div>
                        <div class="info-grid">
                            <div class="info">
                                <span>Payment Status</span>
                                <strong>{{ $sale->paymentStatusLabel() }}</strong>
                            </div>
                            <div class="info">
                                <span>Created Date</span>
                                <strong>{{ $sale->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Created By</span>
                                <strong>{{ $sale->createdBy?->name ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Total Items</span>
                                <strong>{{ number_format($sale->items->count()) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section block">
                    <div class="section-title">Items</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Battery</th>
                                <th class="number">Qty</th>
                                <th class="number">Unit Price</th>
                                <th class="number">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->battery?->brand }} {{ $item->battery?->model }}</strong>
                                        <small>{{ $item->battery?->battery_code ?: 'Battery removed' }}</small>
                                    </td>
                                    <td class="number">{{ number_format($item->quantity) }}</td>
                                    <td class="number">{{ \App\Helpers\CurrencyHelper::format($item->unit_price) }}</td>
                                    <td class="number">{{ \App\Helpers\CurrencyHelper::format($item->total_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="block totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($sale->subtotal) }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Discount</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($sale->discount) }}</strong>
                    </div>
                    <div class="total-row">
                        <span>VAT</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($sale->vat) }}</strong>
                    </div>
                    <div class="total-row grand-total">
                        <span>Grand Total</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($sale->total_amount) }}</strong>
                    </div>
                </div>

                <div class="section block">
                    <div class="section-title">Notes</div>
                    <div class="note">{{ $sale->notes ?: 'No notes added for this sale.' }}</div>
                </div>

                <div class="signature-grid">
                    <div class="signature">Customer Signature</div>
                    <div class="signature">Authorized Signature</div>
                </div>

                <div class="footer-note">
                    Thank you for choosing {{ $brandName }}. Keep this slip for sale confirmation, warranty reference, and payment verification.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
