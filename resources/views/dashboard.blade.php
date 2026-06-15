<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Inventory Intelligence</h1>
            <p class="page-subtitle">Compact overview of stock health, service flow, and sales momentum.</p>
        </div>
    </x-slot>

    <section class="dashboard-grid">
        <div class="dashboard-metrics">
            <div class="stat-card">
                <div class="stat-icon">C</div>
                <div>
                    <p class="stat-meta">Total Customers</p>
                    <p class="stat-value">128</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">B</div>
                <div>
                    <p class="stat-meta">Battery Inventory</p>
                    <p class="stat-value">342</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">R</div>
                <div>
                    <p class="stat-meta">Pending Repairs</p>
                    <p class="stat-value">18</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">S</div>
                <div>
                    <p class="stat-meta">Today Sales</p>
                    <p class="stat-value">{{ \App\Helpers\CurrencyHelper::format(4920) }}</p>
                </div>
            </div>
        </div>

        <div class="dashboard-table-row">
            <div class="table-card">
                <div class="table-card-header">
                    <h2>Recent Repair Jobs</h2>
                    <a href="#" class="view-all-btn btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Customer</th>
                            <th>Device</th>
                            <th>Status</th>
                            <th>ETA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#7124</td>
                            <td>Samuel Hart</td>
                            <td>HP Pavilion</td>
                            <td><span class="status-badge warning">In Service</span></td>
                            <td>Today</td>
                        </tr>
                        <tr>
                            <td>#7119</td>
                            <td>Jasmine K.</td>
                            <td>Lenovo ThinkPad</td>
                            <td><span class="status-badge info">Awaiting Parts</span></td>
                            <td>2d</td>
                        </tr>
                        <tr>
                            <td>#7107</td>
                            <td>Mark Silva</td>
                            <td>Dell Latitude</td>
                            <td><span class="status-badge success">Completed</span></td>
                            <td>Delivered</td>
                        </tr>
                        <tr>
                            <td>#7131</td>
                            <td>Priya Nair</td>
                            <td>Asus ZenBook</td>
                            <td><span class="status-badge warning">Quality Check</span></td>
                            <td>Tomorrow</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h2>Low Stock Batteries</h2>
                    <a href="#" class="view-all-btn btn btn-sm btn-outline-secondary">Reorder</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Battery A32-K52</td>
                            <td>Asus</td>
                            <td>4</td>
                            <td><span class="status-badge danger">Critical</span></td>
                        </tr>
                        <tr>
                            <td>Battery BTY-S14</td>
                            <td>Acer</td>
                            <td>7</td>
                            <td><span class="status-badge warning">Low</span></td>
                        </tr>
                        <tr>
                            <td>Battery L15M4PB0</td>
                            <td>Lenovo</td>
                            <td>9</td>
                            <td><span class="status-badge warning">Low</span></td>
                        </tr>
                        <tr>
                            <td>Battery A41N1423</td>
                            <td>Asus</td>
                            <td>11</td>
                            <td><span class="status-badge info">Restock soon</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-panels">
            <div class="summary-card">
                <p class="summary-title">Today's Sales Summary</p>
                <p class="summary-value">{{ \App\Helpers\CurrencyHelper::format(4920) }}</p>
                <p class="summary-subtext">Strong morning performance across invoice capture and POS activity. Keep stock and repairs balanced for the afternoon rush.</p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <span class="badge-pill success">+12% vs yesterday</span>
                    <span class="badge-pill neutral">5 invoices today</span>
                    <span class="badge-pill warning">2 pending payments</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h2>Operations Snapshot</h2>
                    <a href="#" class="view-all-btn btn btn-sm btn-outline-secondary">Manage</a>
                </div>
                <div class="sales-summary">
                    <div>
                        <p class="summary-title">Open Service Requests</p>
                        <p class="summary-value">18</p>
                    </div>
                    <div>
                        <p class="summary-title">Battery Alerts</p>
                        <p class="summary-value">9</p>
                    </div>
                    <div>
                        <p class="summary-title">Active Customers</p>
                        <p class="summary-value">128</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
