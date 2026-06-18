<x-app-layout>
    <x-slot name="pageTitle">Users & Roles</x-slot>
    <x-slot name="pageBreadcrumb">Home / Users & Roles</x-slot>

    <section class="module-page users-page">
        <div class="users-page-header">
            <div>
                <h2>User Accounts</h2>
                <p>Manage system access, account roles, and login status.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn users-add-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14"></path>
                    <path d="M5 12h14"></path>
                </svg>
                <span>Add User</span>
            </a>
        </div>

        <div class="users-summary">
            <div><span>Total Users</span><strong>{{ number_format($summary['total']) }}</strong></div>
            <div><span>Active Accounts</span><strong>{{ number_format($summary['active']) }}</strong></div>
            <div><span>Customers & Managers</span><strong>{{ number_format($summary['customers'] + $summary['managers']) }}</strong></div>
            <div><span>Managers</span><strong>{{ number_format($summary['managers']) }}</strong></div>
        </div>

        <div class="table-card users-records-card">
            <div class="users-table-header">
                <form method="GET" action="{{ route('users.index') }}" class="users-filter-form">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control users-search" placeholder="Search name or email">
                    <div class="users-filter-controls">
                        <select name="role" class="form-control form-select">
                            <option value="">All Roles</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-control form-select">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-brand">Filter</button>
                        <a href="{{ route('users.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-scroll">
                <table class="module-table users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Linked Customer</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $managedUser)
                            <tr>
                                <td>
                                    <div class="users-identity">
                                        <span class="users-avatar">{{ strtoupper(substr($managedUser->name, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $managedUser->name }}</strong>
                                            @if ($managedUser->is(Auth::user()))
                                                <small>Current account</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $managedUser->email }}</td>
                                <td><span class="user-role-badge role-{{ $managedUser->role }}">{{ $roles[$managedUser->role] ?? ucfirst($managedUser->role) }}</span></td>
                                <td>
                                    @if ($managedUser->customer)
                                        <span class="code-text">{{ $managedUser->customer->customer_code }}</span>
                                        <small class="users-linked-name">{{ $managedUser->customer->full_name }}</small>
                                    @else
                                        <span class="users-muted">Not linked</span>
                                    @endif
                                </td>
                                <td><span class="status-badge customer-status-{{ $managedUser->status }}">{{ $statuses[$managedUser->status] ?? ucfirst($managedUser->status) }}</span></td>
                                <td>{{ $managedUser->created_at?->format('d M Y') ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $managedUser) }}" class="action-btn icon-action" title="Edit user" aria-label="Edit user">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="no-results-cell">No user accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="customers-pagination-footer">
                    <p>Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results</p>
                    <div class="pagination-wrap">{{ $users->links() }}</div>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
