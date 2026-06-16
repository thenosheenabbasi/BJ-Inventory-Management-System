<x-app-layout>
    <x-slot name="pageTitle">{{ $canManage ? 'Repair Battery' : 'My Repair Battery' }}</x-slot>
    <x-slot name="pageBreadcrumb">Home / {{ $canManage ? 'Repair Battery' : 'My Repair Battery' }}</x-slot>

    <section class="module-page customers-modern-page repair-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Track repair intake, diagnosis, payments, QR codes, and delivery status.</p>
            </div>
            @if ($canManage)
                <a href="{{ route('repair-jobs.create') }}" class="btn btn-brand btn-compact">+ Add Repair Battery</a>
            @endif
        </div>

        <div class="table-card customer-records-card customers-modern-card repair-modern-card">
            @php
                $visibleRepairJobs = $repairJobs->getCollection();
                $visibleTotalAmount = $visibleRepairJobs->sum('estimated_cost');
                $visibleTotalAdvance = $visibleRepairJobs->sum('advance_payment');
                $visibleTotalRemaining = $visibleRepairJobs->sum(fn ($job) => $job->remainingAmount());
            @endphp

            <div class="table-card-header customer-records-header customers-modern-card-header repair-modern-card-header">
                <h2>Repair Battery Records</h2>
                @if ($canManage)
                    <form method="GET" action="{{ route('repair-jobs.index') }}" class="repair-search-form">
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search job, customer, phone..." aria-label="Repair job search">
                    </form>
                @endif
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table repair-modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Battery</th>
                            <th>Status</th>
                            <th class="amount-cell">Unit Price</th>
                            <th class="amount-cell">Qty</th>
                            <th class="amount-cell">Total Amount</th>
                            <th class="amount-cell">Advance</th>
                            <th class="amount-cell">Remaining</th>
                            <th>Expected Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($repairJobs as $repairJob)
                            <tr>
                                <td><span class="code-text">{{ $repairJob->repair_number }}</span></td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $repairJob->customer?->full_name ?: '-' }}</strong>
                                        <small>{{ $repairJob->customer?->phone ?: '-' }}</small>
                                    </div>
                                </td>
                                <td class="repair-description-cell">{{ \Illuminate\Support\Str::limit($repairJob->battery_details, 48) }}</td>
                                <td><span class="status-badge {{ $repairJob->statusBadgeClass() }}">{{ $repairJob->statusLabel() }}</span></td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($repairJob->unit_price ?? 0) }}</td>
                                <td class="amount-cell">{{ number_format($repairJob->quantity ?? 1) }}</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($repairJob->estimated_cost) }}</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($repairJob->advance_payment) }}</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($repairJob->remainingAmount()) }}</td>
                                <td>{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View repair battery" aria-label="View repair battery" data-bs-toggle="modal" data-bs-target="#repairDetailsModal-{{ $repairJob->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <a href="{{ route('repair-jobs.slip', $repairJob) }}" class="action-btn icon-action" title="Print slip" aria-label="Print slip" target="_blank">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M7 8V4h10v4"/><path d="M6 17H5a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3h-1"/><path d="M7 14h10v6H7z"/><path d="M17 12h.01"/></svg>
                                        </a>
                                        @if ($canManage)
                                            <a href="{{ route('repair-jobs.edit', $repairJob) }}" class="action-btn icon-action" title="Edit repair battery" aria-label="Edit repair battery">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                            </a>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('repair-jobs.destroy', $repairJob) }}" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn icon-action danger" title="Delete repair battery" aria-label="Delete repair battery">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="no-results-cell">No repair battery records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($repairJobs->count() > 0)
                        <tfoot>
                            <tr class="repair-total-row">
                                <td colspan="6">Total</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($visibleTotalAmount) }}</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($visibleTotalAdvance) }}</td>
                                <td class="amount-cell">{{ \App\Helpers\CurrencyHelper::format($visibleTotalRemaining) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if ($repairJobs->total() > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $repairJobs->firstItem() ?? 0 }} to {{ $repairJobs->lastItem() ?? 0 }} of {{ $repairJobs->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $repairJobs->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($repairJobs as $repairJob)
            <div class="modal fade customer-details-modal repair-details-modal" id="repairDetailsModal-{{ $repairJob->id }}" tabindex="-1" aria-labelledby="repairDetailsModalLabel-{{ $repairJob->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="customer-modal-header inventory-modal-header">
                            <div class="customer-modal-heading">
                                <span class="repair-modal-code">{{ $repairJob->repair_number }}</span>
                                <h2 id="repairDetailsModalLabel-{{ $repairJob->id }}">{{ $repairJob->battery_details ?: '-' }}</h2>
                                <p class="repair-modal-customer">{{ $repairJob->customer?->full_name ?: '-' }}</p>
                            </div>
                            <div class="inventory-modal-meta">
                                <div>
                                    <span>Expected Date</span>
                                    <strong>{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</strong>
                                </div>
                            </div>
                            <button type="button" class="customer-modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="modal-body customer-modal-body">
                            <div class="repair-tabs repair-modal-tabs" role="tablist" aria-label="Repair detail sections">
                                <button type="button" class="repair-tab active" id="repairOverviewTab-{{ $repairJob->id }}" data-bs-toggle="tab" data-bs-target="#repairOverviewPane-{{ $repairJob->id }}" role="tab" aria-controls="repairOverviewPane-{{ $repairJob->id }}" aria-selected="true">
                                    Overview
                                </button>
                                <button type="button" class="repair-tab" id="repairTimelineTab-{{ $repairJob->id }}" data-bs-toggle="tab" data-bs-target="#repairTimelinePane-{{ $repairJob->id }}" role="tab" aria-controls="repairTimelinePane-{{ $repairJob->id }}" aria-selected="false">
                                    Timeline
                                </button>
                                <button type="button" class="repair-tab" id="repairPaymentsTab-{{ $repairJob->id }}" data-bs-toggle="tab" data-bs-target="#repairPaymentsPane-{{ $repairJob->id }}" role="tab" aria-controls="repairPaymentsPane-{{ $repairJob->id }}" aria-selected="false">
                                    Payments
                                </button>
                                <button type="button" class="repair-tab" id="repairAttachmentsTab-{{ $repairJob->id }}" data-bs-toggle="tab" data-bs-target="#repairAttachmentsPane-{{ $repairJob->id }}" role="tab" aria-controls="repairAttachmentsPane-{{ $repairJob->id }}" aria-selected="false">
                                    Attachments
                                </button>
                                <button type="button" class="repair-tab" id="repairCustomerTab-{{ $repairJob->id }}" data-bs-toggle="tab" data-bs-target="#repairCustomerPane-{{ $repairJob->id }}" role="tab" aria-controls="repairCustomerPane-{{ $repairJob->id }}" aria-selected="false">
                                    Customer
                                </button>
                            </div>

                            <div class="tab-content repair-modal-tab-content">
                                <div class="tab-pane fade show active" id="repairOverviewPane-{{ $repairJob->id }}" role="tabpanel" aria-labelledby="repairOverviewTab-{{ $repairJob->id }}" tabindex="0">
                                    <div class="repair-detail-summary">
                                        <div class="table-card repair-qr-card">
                                            <div class="repair-qr-box">
                                                @if ($repairJob->qrCode)
                                                    {!! $repairJob->qrCode->svgMarkup(156) !!}
                                                @else
                                                    <span>No QR</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span>QR Code</span>
                                                <strong>{{ $repairJob->repair_number }}</strong>
                                                <p>Generated automatically for this repair battery record.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="customer-detail-grid inventory-detail-grid">
                                        <div class="customer-detail-item">
                                            <span>Status</span>
                                            <strong><span class="status-badge {{ $repairJob->statusBadgeClass() }}">{{ $repairJob->statusLabel() }}</span></strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Created Date</span>
                                            <strong>{{ $repairJob->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Phone</span>
                                            <strong>{{ $repairJob->customer?->phone ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Expected Delivery</span>
                                            <strong>{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Created By</span>
                                            <strong>{{ $repairJob->createdBy?->name ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Last Updated</span>
                                            <strong>{{ $repairJob->updated_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                        </div>
                                    </div>

                                    <div class="customer-notes-panel">
                                        <span>Issue Description</span>
                                        <p>{{ $repairJob->issue_description ?: 'No issue description added.' }}</p>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="repairTimelinePane-{{ $repairJob->id }}" role="tabpanel" aria-labelledby="repairTimelineTab-{{ $repairJob->id }}" tabindex="0">
                                    <div class="repair-timeline">
                                        @forelse ($repairJob->timelines->sortByDesc('created_at') as $timeline)
                                            <div class="repair-timeline-item">
                                                <div class="repair-timeline-dot"></div>
                                                <div>
                                                    <strong>{{ $statuses[$timeline->to_status] ?? ucfirst(str_replace('_', ' ', $timeline->to_status)) }}</strong>
                                                    <span>{{ $timeline->created_at?->format('d M Y, h:i A') }} - {{ $timeline->changedBy?->name ?: 'System' }}</span>
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
                                </div>

                                <div class="tab-pane fade" id="repairPaymentsPane-{{ $repairJob->id }}" role="tabpanel" aria-labelledby="repairPaymentsTab-{{ $repairJob->id }}" tabindex="0">
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
                                                        <td>{{ $payment->code() }}</td>
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
                                </div>

                                <div class="tab-pane fade" id="repairAttachmentsPane-{{ $repairJob->id }}" role="tabpanel" aria-labelledby="repairAttachmentsTab-{{ $repairJob->id }}" tabindex="0">
                                    <div class="empty-state">
                                        <strong>No attachments added</strong>
                                        <span>Repair photos and documents will appear here once attachment upload is connected.</span>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="repairCustomerPane-{{ $repairJob->id }}" role="tabpanel" aria-labelledby="repairCustomerTab-{{ $repairJob->id }}" tabindex="0">
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

                                    <div class="customer-detail-grid inventory-detail-grid">
                                        <div class="customer-detail-item">
                                            <span>Customer Code</span>
                                            <strong>{{ $repairJob->customer?->customer_code ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>Phone</span>
                                            <strong>{{ $repairJob->customer?->phone ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>WhatsApp</span>
                                            <strong>{{ $repairJob->customer?->whatsapp ?: '-' }}</strong>
                                        </div>
                                        <div class="customer-detail-item">
                                            <span>City</span>
                                            <strong>{{ $repairJob->customer?->city ?: '-' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="customer-modal-footer">
                            <a href="{{ route('repair-jobs.slip', $repairJob) }}" class="btn btn-light" target="_blank">Print Slip</a>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</x-app-layout>
