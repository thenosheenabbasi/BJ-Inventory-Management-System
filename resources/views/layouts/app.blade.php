<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'M. Bilal jamshed') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-N5vRtFetEfE9OdLDKnsXHpswJ4cyGmX5a8kTzadxi+u0i7GoCmBbJZzZH+OiJhCE" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') ?: $errors->first() }}"
    data-flash-warning="{{ session('warning') }}"
    data-flash-info="{{ session('info') }}"
>
    @php
        $brandName = 'M. Bilal jamshed';
        $userRole = Auth::user()->role ?? 'customer';
        $displayRole = $userRole === 'admin' ? 'Administrator' : ucwords($userRole);
        $menuItems = [];
        if ($userRole === 'admin') {
            $menuItems = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => 'dashboard'],
                ['label' => 'Customers', 'href' => route('customers.index'), 'active' => 'customers.*'],
                ['label' => 'Battery Inventory', 'href' => route('battery-inventory.index'), 'active' => 'battery-inventory.*'],
                ['label' => 'Repair Battery', 'href' => route('repair-jobs.index'), 'active' => 'repair-jobs.*'],
                ['label' => 'Sales', 'href' => route('sales.index'), 'active' => 'sales.*'],
                ['label' => 'Payments', 'href' => route('payments.index'), 'active' => 'payments.*'],
                ['label' => 'Reports', 'href' => '#'],
                ['label' => 'Suppliers', 'href' => route('suppliers.index'), 'active' => 'suppliers.*'],
                ['label' => 'Users & Roles', 'href' => '#'],
                ['label' => 'Settings', 'href' => '#'],
            ];
        } elseif ($userRole === 'manager') {
            $menuItems = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => 'dashboard'],
                ['label' => 'Customers', 'href' => route('customers.index'), 'active' => 'customers.*'],
                ['label' => 'Battery Inventory', 'href' => route('battery-inventory.index'), 'active' => 'battery-inventory.*'],
                ['label' => 'Repair Battery', 'href' => route('repair-jobs.index'), 'active' => 'repair-jobs.*'],
                ['label' => 'Sales', 'href' => route('sales.index'), 'active' => 'sales.*'],
                ['label' => 'Payments', 'href' => route('payments.index'), 'active' => 'payments.*'],
                ['label' => 'Reports', 'href' => '#'],
                ['label' => 'Suppliers', 'href' => route('suppliers.index'), 'active' => 'suppliers.*'],
            ];
        } else {
            $menuItems = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => 'dashboard'],
                ['label' => 'My Repair Battery', 'href' => route('repair-jobs.index'), 'active' => 'repair-jobs.*'],
                ['label' => 'My Purchases', 'href' => '#'],
                ['label' => 'My Payments', 'href' => route('payments.index'), 'active' => 'payments.*'],
                ['label' => 'My Invoices', 'href' => '#'],
                ['label' => 'Profile', 'href' => route('profile.edit')],
            ];
        }
    @endphp

    <div class="app-shell">
        <aside class="app-sidebar" aria-label="Primary navigation">
            <button class="sidebar-close" type="button" aria-label="Close navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>

            <div class="sidebar-brand">
                <div class="sidebar-brand-text">
                    <span>{{ $brandName }}</span>
                    <strong>Inventory Management System</strong>
                </div>
            </div>

            <nav class="sidebar-nav">
                @foreach ($menuItems as $item)
                    <a href="{{ $item['href'] }}" class="sidebar-link {{ isset($item['active']) && request()->routeIs($item['active']) ? 'active' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <p>© 2026 {{ $brandName }}</p>
                <p class="sidebar-footer-secondary">All rights reserved.</p>
            </div>
        </aside>

        <main class="app-main">
            <header class="app-topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <div>
                        <h1>{{ $pageTitle ?? 'Dashboard' }}</h1>
                        <div class="breadcrumb">{{ $pageBreadcrumb ?? 'Home / Dashboard' }}</div>
                    </div>
                </div>

                <div class="topbar-right">
                    <button class="btn btn-icon btn-search" aria-label="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <button class="btn btn-icon btn-notification" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="notification-badge">5</span>
                    </button>
                    <div class="dropdown user-dropdown">
                        <button class="user-profile-btn dropdown-toggle" type="button" id="userProfileDropdown" data-profile-dropdown-toggle aria-expanded="false">
                            <span class="avatar" aria-hidden="true">
                                <img src="{{ asset('assets/images/m-bilal-avatar.png') }}" alt="" />
                            </span>

                            <span class="user-details">
                                <strong>{{ Auth::user()->name }}</strong>
                                <small>{{ $displayRole }}</small>
                            </span>

                            <span class="dropdown-arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu profile-menu" aria-labelledby="userProfileDropdown">
                            <li>
                                <a class="dropdown-item profile-menu-link" href="{{ route('profile.edit') }}">
                                    <span class="profile-menu-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.4 1.07V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 8.6 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.07-.4H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .4-1.07V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15.4 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.36.28.6.68.6 1.1H20a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15z"/></svg>
                                    </span>
                                    <span>Account Settings</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item profile-menu-link" href="{{ route('profile.edit') }}#change-password">
                                    <span class="profile-menu-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </span>
                                    <span>Change Password</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider profile-menu-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item profile-menu-link profile-menu-danger logout-item">
                                        <span class="profile-menu-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        </span>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="app-content">
                {{ $slot }}
            </section>
        </main>
    </div>

    <div class="sidebar-backdrop"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
</body>
</html>
