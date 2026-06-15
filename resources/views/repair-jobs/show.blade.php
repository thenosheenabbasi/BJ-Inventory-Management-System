<x-app-layout>
    <x-slot name="pageTitle">Repair Details</x-slot>
    <x-slot name="pageBreadcrumb">Home / Repair Battery / {{ $repairJob->repair_number }}</x-slot>

    <section class="module-page repair-detail-page">
        <div class="module-header repair-detail-header">
            <div>
                <h2>{{ $repairJob->repair_number }}</h2>
                <p>{{ $repairJob->customer?->full_name ?: '-' }} · {{ $repairJob->statusLabel() }}</p>
            </div>
            <div class="module-actions">
                <a href="{{ route('repair-jobs.index') }}" class="btn btn-light">Back</a>
                <a href="{{ route('repair-jobs.slip', $repairJob) }}" class="btn btn-light" target="_blank">Print Slip</a>
                @if ($canManage)
                    <a href="{{ route('repair-jobs.edit', $repairJob) }}" class="btn btn-brand">Edit Repair Battery</a>
                @endif
                @if ($canDelete)
                    <form method="POST" action="{{ route('repair-jobs.destroy', $repairJob) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="repair-detail-summary">
            <div class="table-card repair-qr-card">
                <div class="repair-qr-box">
                    {!! $repairJob->qrCode?->svgMarkup(156) !!}
                </div>
                <div>
                    <span>QR Code</span>
                    <strong>{{ $repairJob->repair_number }}</strong>
                    <p>Generated automatically for this repair battery record.</p>
                </div>
            </div>

            <div class="repair-metric-card">
                <span>Quantity</span>
                <strong>{{ number_format($repairJob->quantity ?? 1) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Unit Price</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->unit_price ?? 0) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Amount</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->estimated_cost) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Advance</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->advance_payment) }}</strong>
            </div>
            <div class="repair-metric-card">
                <span>Remaining</span>
                <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->remainingAmount()) }}</strong>
            </div>
        </div>

        <div class="table-card repair-detail-card">
            <div class="repair-tabs" role="tablist" aria-label="Repair detail sections">
                @foreach ($tabs as $tab)
                    <a href="{{ route('repair-jobs.show', ['repairJob' => $repairJob, 'tab' => $tab]) }}" class="repair-tab {{ $activeTab === $tab ? 'active' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $tab)) }}
                    </a>
                @endforeach
            </div>

            <div class="repair-tab-panel">
                @if ($activeTab === 'overview')
                    <div class="detail-grid repair-overview-grid">
                        <div class="detail-item">
                            <span>Code</span>
                            <strong>{{ $repairJob->repair_number }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Status</span>
                            <strong><span class="status-badge {{ $repairJob->statusBadgeClass() }}">{{ $repairJob->statusLabel() }}</span></strong>
                        </div>
                        <div class="detail-item">
                            <span>Quantity</span>
                            <strong>{{ number_format($repairJob->quantity ?? 1) }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Unit Price</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->unit_price ?? 0) }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Expected Delivery</span>
                            <strong>{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Created By</span>
                            <strong>{{ $repairJob->createdBy?->name ?: '-' }}</strong>
                        </div>
                    </div>

                    <div class="notes-box">
                        <span>Battery Model / Name</span>
                        <p>{{ $repairJob->battery_details }}</p>
                    </div>
                    <div class="notes-box">
                        <span>Issue Description</span>
                        <p>{{ $repairJob->issue_description }}</p>
                    </div>
                @elseif ($activeTab === 'timeline')
                    <div class="repair-timeline">
                        @forelse ($repairJob->timelines->sortByDesc('created_at') as $timeline)
                            <div class="repair-timeline-item">
                                <div class="repair-timeline-dot"></div>
                                <div>
                                    <strong>{{ $statuses[$timeline->to_status] ?? ucfirst(str_replace('_', ' ', $timeline->to_status)) }}</strong>
                                    <span>{{ $timeline->created_at?->format('d M Y, h:i A') }} · {{ $timeline->changedBy?->name ?: 'System' }}</span>
                                    @if ($timeline->from_status)
                                        <p>Changed from {{ $statuses[$timeline->from_status] ?? ucfirst(str_replace('_', ' ', $timeline->from_status)) }}.</p>
                                    @endif
                                    @if ($timeline->notes)
                                        <p>{{ $timeline->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <strong>No timeline yet</strong>
                                <span>Status changes will appear here automatically.</span>
                            </div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'payments')
                    <div class="repair-payment-grid">
                        <div class="repair-metric-card">
                            <span>Amount</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->estimated_cost) }}</strong>
                        </div>
                        <div class="repair-metric-card">
                            <span>Advance Paid</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->advance_payment) }}</strong>
                        </div>
                        <div class="repair-metric-card">
                            <span>Remaining</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($repairJob->remainingAmount()) }}</strong>
                        </div>
                    </div>

                    <div class="table-scroll repair-payments-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Payment No</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Created By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($repairJob->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>{{ ucfirst($payment->payment_type) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                                        <td>{{ \App\Helpers\CurrencyHelper::format($payment->amount) }}</td>
                                        <td>{{ $payment->createdBy?->name ?: '-' }}</td>
                                        <td>{{ $payment->created_at?->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="no-results-cell">No payments recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @elseif ($activeTab === 'attachments')
                    <div class="empty-state">
                        <strong>No attachments added</strong>
                        <span>Repair photos and documents will appear here once attachment upload is connected.</span>
                    </div>
                @elseif ($activeTab === 'customer')
                    <div class="profile-summary repair-customer-summary">
                        <div class="profile-avatar">{{ strtoupper(substr($repairJob->customer?->full_name ?: 'C', 0, 1)) }}</div>
                        <div>
                            <h3>{{ $repairJob->customer?->full_name ?: '-' }}</h3>
                            <p>{{ $repairJob->customer?->email ?: 'No email added' }}</p>
                            <span class="status-badge customer-status-{{ $repairJob->customer?->status ?: 'inactive' }}">
                                {{ ucfirst($repairJob->customer?->status ?: 'inactive') }}
                            </span>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Customer Code</span>
                            <strong>{{ $repairJob->customer?->customer_code ?: '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Phone</span>
                            <strong>{{ $repairJob->customer?->phone ?: '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>WhatsApp</span>
                            <strong>{{ $repairJob->customer?->whatsapp ?: '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>City</span>
                            <strong>{{ $repairJob->customer?->city ?: '-' }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app-layout>
