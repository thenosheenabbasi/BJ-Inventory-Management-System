@php
    $customerName = $repairJob->customer?->full_name ?: 'Customer';
    $customerCode = $repairJob->customer?->customer_code ?: 'NA';
    $brandName = 'M. Bilal jamshed';
    $slipTitle = $customerName.' '.$repairJob->repair_number;
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

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 10mm;
            background:
                radial-gradient(circle at top left, rgba(39, 39, 87, 0.06), transparent 32%),
                radial-gradient(circle at bottom right, rgba(39, 39, 87, 0.07), transparent 34%),
                #ffffff;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.12);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            line-height: 1;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid var(--line);
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

        .slip {
            border: 1px solid var(--line);
            border-left: 8px solid var(--brand);
            border-radius: 12px;
            overflow: hidden;
            min-height: calc(297mm - 20mm);
            display: flex;
            flex-direction: column;
            position: relative;
            background:
                linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.94)),
                #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .slip::before {
            content: "M. Bilal Jamshed";
            position: absolute;
            top: 56%;
            left: 50%;
            width: 100%;
            text-align: center;
            color: rgba(39, 39, 87, 0.12);
            font-size: 58px;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            transform: translate(-50%, -50%) rotate(-8deg);
            transform-origin: center;
            pointer-events: none;
        }

        .slip::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(135deg, rgba(39, 39, 87, 0.04) 0 1px, transparent 1px 18px);
            background-position: 0 0;
            background-size: 100% 100%;
            background-repeat: no-repeat;
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
            gap: 8px;
            align-items: center;
            padding: 24px 18px 16px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.78);
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-width: 0;
            margin-top: 14px;
        }

        .brand h1 {
            margin: 0;
            font-size: 26px;
            line-height: 1;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .brand p,
        .meta p {
            margin: 5px 0 0;
            color: var(--muted);
        }

        .brand p {
            margin: 0;
            line-height: 1;
            white-space: nowrap;
        }

        .meta {
            text-align: right;
            font-size: 12px;
        }

        .meta strong {
            display: block;
            margin-top: 5px;
            color: var(--ink);
            font-size: 14px;
        }

        .slip-body {
            padding: 36px 18px 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 150px;
            gap: 16px;
            align-items: stretch;
        }

        .section {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.78);
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

        .details-section {
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: 0 12px;
        }

        .details-section .section-title {
            padding: 0 0 9px;
            margin-bottom: 14px;
            background: transparent;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            letter-spacing: 0.08em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px 22px;
        }

        .info {
            min-height: 0;
            padding: 6px 0;
            border-bottom: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
            align-items: flex-start;
        }

        .info:nth-child(odd) {
            border-right: 0;
        }

        .info-full {
            grid-column: 1 / -1;
        }

        .info span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info span::after {
            content: "";
        }

        .info strong {
            display: block;
            margin-top: 0;
            color: var(--ink);
            font-size: 13px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .qr-card {
            min-height: 100%;
            padding: 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .qr-card svg {
            width: 126px;
            height: 126px;
            display: block;
            margin: 0 auto;
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        .qr-card span {
            display: block;
            margin-top: 9px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .block {
            margin-top: 28px;
        }

        .note {
            padding: 12px;
            min-height: 72px;
            line-height: 1.55;
        }

        .payment-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border-top: 0;
            padding: 10px 0;
        }

        .payment-row .info {
            border-right: 0;
            border-bottom: 0;
        }

        .payment-row .info:last-child {
            border-right: 0;
        }

        .payment-row .info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 7px;
            min-height: 44px;
            padding: 9px 12px;
        }

        .payment-row .info span,
        .payment-row .info strong {
            display: block;
            margin-top: 0;
            white-space: nowrap;
        }

        .payment-row .info span::after {
            content: "";
        }

        .payment-row .info strong {
            overflow: visible;
            text-overflow: clip;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 64px;
            margin-top: 0;
            padding-top: 0;
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
            border-top: 4px solid var(--brand);
            border-radius: 8px;
            background: var(--brand);
            color: #ffffff;
            font-size: 11px;
            line-height: 1.45;
            text-align: center;
        }

        .slip-footer {
            margin-top: auto;
            padding-top: 58px;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media (max-width: 760px) {
            .top-grid,
            .info-grid,
            .payment-row {
                grid-template-columns: 1fr;
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
                grid-template-columns: 1fr 150px;
            }

            .info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .payment-row {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .slip {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('repair-jobs.index') }}" class="btn">Back</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="page">
        <section class="slip">
            <header class="slip-header">
                <div class="brand">
                    <h1>{{ $brandName }}</h1>
                    <p>Inventory Management System</p>
                </div>
                <div class="meta">
                    <p>Slip No</p>
                    <strong>{{ $repairJob->repair_number }}</strong>
                    <p>{{ now()->format('d M Y, h:i A') }}</p>
                </div>
            </header>

            <div class="slip-body">
                <div class="top-grid">
                    <div class="section details-section">
                        <div class="section-title">Details</div>
                        <div class="info-grid">
                            <div class="info">
                                <span>Client Name</span>
                                <strong>{{ $customerName }}</strong>
                            </div>
                            <div class="info">
                                <span>Phone</span>
                                <strong>{{ $repairJob->customer?->phone ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Status</span>
                                <strong>{{ $repairJob->statusLabel() }}</strong>
                            </div>
                            <div class="info">
                                <span>Created Date</span>
                                <strong>{{ $repairJob->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Expected Date</span>
                                <strong>{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Battery Details</span>
                                <strong>{{ $repairJob->battery_details ?: '-' }}</strong>
                            </div>
                            <div class="info">
                                <span>Quantity</span>
                                <strong>{{ number_format($repairJob->quantity ?? 1) }}</strong>
                            </div>
                            <div class="info">
                                <span>Unit Price</span>
                                <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->unit_price ?? 0) }}</strong>
                            </div>
                            @if ($repairJob->issue_description)
                                <div class="info info-full">
                                    <span>Issue Description</span>
                                    <strong>{{ $repairJob->issue_description }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="section qr-card">
                        @if ($repairJob->qrCode)
                            {!! $repairJob->qrCode->svgMarkup(126) !!}
                        @else
                            <strong>No QR</strong>
                        @endif
                        <span>Scan for repair record</span>
                    </div>
                </div>

                <div class="section block">
                    <div class="section-title">Payment Summary</div>
                    <div class="payment-row">
                        <div class="info">
                            <span>Amount</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->estimated_cost) }}</strong>
                        </div>
                        <div class="info">
                            <span>Advance Paid</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->advance_payment) }}</strong>
                        </div>
                        <div class="info">
                            <span>Balance Due</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->remainingAmount()) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="slip-footer">
                    <div class="signature-grid">
                        <div class="signature">Client Signature</div>
                        <div class="signature">Authorized Signature</div>
                    </div>

                    <div class="footer-note">
                        Thank you for choosing {{ $brandName }}. Keep this slip for repair tracking, payment confirmation, and pickup verification.
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
