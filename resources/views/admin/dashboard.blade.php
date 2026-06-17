<x-app-layout>
    <x-slot name="header">
        <div class="page-header-title">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <nav class="breadcrumb-list" aria-label="Breadcrumb">
                    <ol>
                        <li><a href="{{ route('dashboard') }}">Home</a></li>
                        <li><span>Dashboard</span></li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>

    <section class="dashboard-page">
        <div class="kpi-grid">
            <article class="kpi-card">
                <div class="kpi-icon icon-customers" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Customers</p>
                    <p class="kpi-value">{{ number_format($dashboardStats['totalCustomers'] ?? 0) }}</p>
                    <p class="kpi-trend text-success">{{ number_format($dashboardStats['activeCustomers'] ?? 0) }} active · {{ number_format($dashboardStats['customersThisMonth'] ?? 0) }} new this month</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-battery" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="18" height="12" rx="2" />
                        <path d="M22 10h0" />
                        <rect x="6" y="9" width="8" height="6" rx="1" fill="currentColor" opacity="0.17" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Battery Stock</p>
                    <p class="kpi-value">{{ number_format($dashboardStats['totalBatteryStock'] ?? 0) }}</p>
                    <p class="kpi-trend text-success">{{ number_format($dashboardStats['totalBatteries'] ?? 0) }} items · {{ number_format($dashboardStats['activeBatteries'] ?? 0) }} active</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-repair" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 21h14" />
                        <path d="M6.5 10.5l5-5 2.5 2.5 4-4" />
                        <path d="M13 3.5l3.5 3.5" />
                        <path d="M11 11l3 3" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Repair Jobs</p>
                    <p class="kpi-value">{{ number_format($dashboardStats['totalRepairJobs'] ?? 0) }}</p>
                    <p class="kpi-trend text-warning">{{ number_format($dashboardStats['pendingRepairs'] ?? 0) }} pending · {{ number_format($dashboardStats['completedRepairs'] ?? 0) }} delivered</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-alert" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Monthly Pending</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyPendingTotal'] ?? 0) }}</p>
                    <p class="kpi-trend text-warning">Sale + repair pending this month</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-sales" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 12h12" />
                        <path d="M6 18h12" />
                        <path d="M6 6h12" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Today Business</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['todayBusinessAmount'] ?? 0) }}</p>
                    <p class="kpi-trend text-success">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['todayBusinessReceived'] ?? 0) }} received today</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-payments" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="7" width="18" height="12" rx="2" />
                        <path d="M3 11h18" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Pending</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['totalPendingPayments'] ?? 0) }}</p>
                    <p class="kpi-trend text-warning">All pending sale + repair balance</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-complete" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Monthly Total</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyBusinessTotal'] ?? 0) }}</p>
                    <p class="kpi-trend text-success">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlySaleAmount'] ?? 0) }} sale · {{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyRepairAmount'] ?? 0) }} repair</p>
                </div>
            </article>

            <article class="kpi-card">
                <div class="kpi-icon icon-revenue" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="8" />
                        <path d="M10 8h4a2 2 0 0 1 0 4h-2a2 2 0 0 0 0 4" />
                    </svg>
                </div>
                <div class="kpi-content">
                    <p class="kpi-label">Monthly Earning</p>
                    <p class="kpi-value">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyEarningTotal'] ?? 0) }}</p>
                    <p class="kpi-trend text-success">{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyPendingTotal'] ?? 0) }} pending this month</p>
                </div>
            </article>
        </div>

        <div class="dashboard-grid">
            <div class="table-card table-card-large">
                <div class="table-card-header">
                    <h2>Recent Repair Battery</h2>
                    <a href="{{ route('repair-jobs.index') }}" class="view-all-btn btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="table-scroll">
                    <table class="dashboard-repair-table">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Customer</th>
                                <th>Model</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentRepairJobs as $repairJob)
                                <tr>
                                    <td>{{ $repairJob->repair_number }}</td>
                                    <td>{{ $repairJob->customer?->full_name ?: '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($repairJob->battery_details, 18) }}</td>
                                    <td class="text-center"><span class="status-badge {{ $repairJob->statusBadgeClass() }}">{{ $repairJob->statusLabel() }}</span></td>
                                    <td class="text-end">{{ $repairJob->expected_delivery_date?->format('d M Y') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="no-results-cell">No repair battery records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card table-card-large">
                <div class="table-card-header">
                    <h2>Pending Client Amounts</h2>
                    <a href="{{ route('payments.index') }}" class="view-all-btn btn btn-sm btn-outline-secondary">View Payments</a>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Phone</th>
                                <th class="text-end">Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingClientInvoices as $invoice)
                                <tr>
                                    <td><span class="code-text">{{ $invoice['invoice'] }}</span></td>
                                    <td>{{ $invoice['date']?->format('d M Y') ?: '-' }}</td>
                                    <td>{{ $invoice['customer'] }}</td>
                                    <td>{{ $invoice['phone'] }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($invoice['amount']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="no-results-cell">No pending client amounts.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dashboard-bottom">
            <div class="table-card summary-card sales-summary-card">
                <div class="sales-summary-header">
                    <h2>Monthly Sale + Repair Summary</h2>
                    <span class="badge-pill success">Live</span>
                </div>

                <div class="sales-primary-metrics">
                    <div class="sales-metric">
                        <span>Total Month Amount</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyBusinessTotal'] ?? 0) }}</strong>
                    </div>
                    <div class="sales-metric">
                        <span>Total Month Earning</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyEarningTotal'] ?? 0) }}</strong>
                    </div>
                    <div class="sales-metric">
                        <span>Total Month Pending</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyPendingTotal'] ?? 0) }}</strong>
                    </div>
                </div>

                <div class="sales-secondary-metrics">
                    <div>
                        <span>Sale Total</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlySaleAmount'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Repair Total</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyRepairAmount'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Sale Pending</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlySalePending'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Repair Pending</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($dashboardStats['monthlyRepairPending'] ?? 0) }}</strong>
                    </div>
                </div>

                <div class="revenue-mini-chart" aria-hidden="true">
                    <svg viewBox="0 0 720 110" preserveAspectRatio="none">
                        <path class="chart-area" d="M0 86 C80 74 116 78 176 60 C238 41 278 54 332 46 C402 35 444 20 506 32 C590 48 626 26 720 16 L720 110 L0 110 Z" />
                        <path class="chart-line" d="M0 86 C80 74 116 78 176 60 C238 41 278 54 332 46 C402 35 444 20 506 32 C590 48 626 26 720 16" />
                        <circle class="chart-point" cx="176" cy="60" r="4" />
                        <circle class="chart-point" cx="332" cy="46" r="4" />
                        <circle class="chart-point" cx="506" cy="32" r="4" />
                        <circle class="chart-point" cx="720" cy="16" r="4" />
                    </svg>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
