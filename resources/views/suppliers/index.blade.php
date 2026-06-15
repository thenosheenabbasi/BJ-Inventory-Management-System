<x-app-layout>
    <x-slot name="pageTitle">Suppliers</x-slot>
    <x-slot name="pageBreadcrumb">Home / Suppliers</x-slot>

    <section class="module-page customers-modern-page suppliers-modern-page">
        <div class="customer-page-header">
            <div>
                <p>Manage supplier companies, contacts, and status.</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-brand btn-compact">+ Add Supplier</a>
        </div>

        <div class="table-card customer-records-card customers-modern-card suppliers-modern-card">
            <div class="table-card-header customer-records-header customers-modern-card-header suppliers-modern-card-header">
                <h2>Supplier Records</h2>
                <form method="GET" action="{{ route('suppliers.index') }}" class="customer-search-form">
                    <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search suppliers..." aria-label="Supplier search">
                </form>
            </div>

            <div class="table-scroll">
                <table class="module-table customers-modern-table suppliers-modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>WhatsApp</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td><span class="code-text">{{ $supplier->supplier_code }}</span></td>
                                <td>
                                    <div class="customer-name">
                                        <strong>{{ $supplier->company_name }}</strong>
                                        @if ($supplier->email)
                                            <small>{{ $supplier->email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->whatsapp }}</td>
                                <td>{{ $supplier->city ?: '-' }}</td>
                                <td>
                                    <span class="status-badge customer-status-{{ $supplier->status }}">
                                        {{ $statuses[$supplier->status] ?? $supplier->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="action-btn icon-action" title="View supplier" aria-label="View supplier" data-bs-toggle="modal" data-bs-target="#supplierDetailsModal-{{ $supplier->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="action-btn icon-action" title="Edit supplier" aria-label="Edit supplier">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        </a>
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn icon-action danger" title="Delete supplier" aria-label="Delete supplier">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-results-cell">{{ $summary['total'] === 0 ? 'No suppliers found.' : 'No matching suppliers found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > 0)
                <div class="customers-pagination-footer">
                    <p>Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} results</p>
                    <div class="pagination-wrap">
                        {{ $suppliers->links() }}
                    </div>
                </div>
            @endif
        </div>

        @foreach ($suppliers as $supplier)
            <div class="modal fade customer-details-modal supplier-details-modal" id="supplierDetailsModal-{{ $supplier->id }}" tabindex="-1" aria-labelledby="supplierDetailsModalLabel-{{ $supplier->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="customer-modal-header">
                            <div class="customer-modal-avatar" aria-hidden="true">
                                {{ strtoupper(substr($supplier->company_name, 0, 1)) }}
                            </div>
                            <div class="customer-modal-heading">
                                <span>{{ $supplier->supplier_code }}</span>
                                <h2 id="supplierDetailsModalLabel-{{ $supplier->id }}">{{ $supplier->company_name }}</h2>
                                <div class="customer-modal-badges">
                                    <span class="status-badge customer-status-{{ $supplier->status }}">
                                        {{ $statuses[$supplier->status] ?? $supplier->status }}
                                    </span>
                                    <span class="customer-type-pill">
                                        {{ $supplier->contact_person }}
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
                                    <span>Supplier Code</span>
                                    <strong>{{ $supplier->supplier_code }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Company Name</span>
                                    <strong>{{ $supplier->company_name }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Contact Person</span>
                                    <strong>{{ $supplier->contact_person }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Phone</span>
                                    <strong>{{ $supplier->phone }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>WhatsApp</span>
                                    <strong>{{ $supplier->whatsapp }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Email</span>
                                    <strong>{{ $supplier->email ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>City</span>
                                    <strong>{{ $supplier->city ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Country</span>
                                    <strong>{{ $supplier->country ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created By</span>
                                    <strong>{{ $supplier->createdBy?->name ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Created Date</span>
                                    <strong>{{ $supplier->created_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                                <div class="customer-detail-item">
                                    <span>Last Updated</span>
                                    <strong>{{ $supplier->updated_at?->format('d M Y, h:i A') ?: '-' }}</strong>
                                </div>
                            </div>

                            @if ($supplier->address)
                                <div class="customer-notes-panel">
                                    <span>Address</span>
                                    <p>{{ $supplier->address }}</p>
                                </div>
                            @endif

                            <div class="customer-notes-panel">
                                <span>Notes</span>
                                <p>{{ $supplier->notes ?: 'No notes added for this supplier.' }}</p>
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
