<x-app-layout>
    <x-slot name="pageTitle">Customers</x-slot>
    <x-slot name="pageBreadcrumb">Home / Customers</x-slot>

    <section class="module-page customers-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Manage walk in, repair, and purchase customers.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn btn-brand btn-compact">+ Add Customer</a>
        </div>

        <div class="table-card customer-records-card customers-modern-card">
            <div class="table-card-header customer-records-header customers-modern-card-header">
                @if ($summary['total'] > 0)
                    <form method="GET" action="{{ route('customers.index') }}" class="customer-search-form">
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search customers..." aria-label="Customer search">
                    </form>
                @endif
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>WhatsApp</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td><span class="code-text">{{ $customer->customer_code }}</span></td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $customer->full_name }}</strong>
                                        @if ($customer->email)
                                            <small>{{ $customer->email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->whatsapp ?: '-' }}</td>
                                <td class="customer-type-text">{{ $customerTypes[$customer->customer_type] ?? $customer->customer_type }}</td>
                                <td>
                                    <span class="status-badge customer-status-{{ $customer->status }}">
                                        {{ $statuses[$customer->status] ?? $customer->status }}
                                    </span>
                                </td>
                                <td>{{ $customer->created_at?->format('d M Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View customer" aria-label="View customer" data-bs-toggle="modal" data-bs-target="#customerDetailsModal-{{ $customer->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <a href="{{ route('customers.edit', $customer) }}" class="action-btn icon-action" title="Edit customer" aria-label="Edit customer">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        </a>
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn icon-action danger" title="Delete customer" aria-label="Delete customer">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-results-cell">{{ $summary['total'] === 0 ? 'No customers found.' : 'No matching customers found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $customers->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($customers as $customer)
            <div class="modal fade customer-details-modal" id="customerDetailsModal-{{ $customer->id }}" tabindex="-1" aria-labelledby="customerDetailsModalLabel-{{ $customer->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="customer-modal-header">
                            <div class="customer-modal-avatar" aria-hidden="true">
                                {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                            </div>
                            <div class="customer-modal-heading">
                                <span>{{ $customer->customer_code }}</span>
                                <h2 id="customerDetailsModalLabel-{{ $customer->id }}">{{ $customer->full_name }}</h2>
                                <div class="customer-modal-badges">
                                    <span class="status-badge customer-status-{{ $customer->status }}">
                                        {{ $statuses[$customer->status] ?? $customer->status }}
                                    </span>
                                    <span class="customer-type-pill">
                                        {{ $customerTypes[$customer->customer_type] ?? $customer->customer_type }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="customer-modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="modal-body customer-modal-body">
                            <div class="customer-detail-grid">
                                <div class="customer-detail-item">
                                    <span>Phone</span>
                                    <strong>{{ $customer->phone ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>WhatsApp</span>
                                    <strong>{{ $customer->whatsapp ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Email</span>
                                    <strong>{{ $customer->email ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>City</span>
                                    <strong>{{ $customer->city ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Country</span>
                                    <strong>{{ $customer->country ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created By</span>
                                    <strong>{{ $customer->createdBy?->name ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created Date</span>
                                    <strong>{{ $customer->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Last Updated</span>
                                    <strong>{{ $customer->updated_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                            </div>

                            <div class="customer-notes-panel">
                                <span>Notes</span>
                                <p>{{ $customer->notes ?: 'No notes added for this customer.' }}</p>
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
