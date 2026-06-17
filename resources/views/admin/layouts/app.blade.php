<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — RentStuff</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-border: #1e293b;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --accent-light: #eff6ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --text: #1e293b;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -1px rgba(0,0,0,.04);
        }
        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--sidebar-bg);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100;
            border-right: 1px solid var(--sidebar-border);
        }
        .sidebar-logo {
            padding: 24px 20px 20px; border-bottom: 1px solid var(--sidebar-border);
        }
        .sidebar-logo a { text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo-icon {
            width: 36px; height: 36px; background: var(--accent); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .logo-text { font-size: 18px; font-weight: 700; color: #fff; letter-spacing: -.3px; }
        .logo-sub { font-size: 11px; color: var(--gray-400); font-weight: 400; margin-top: 1px; }

        .sidebar-nav { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .nav-section-title {
            font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
            color: var(--gray-400); padding: 8px 10px 6px; margin-top: 8px;
        }
        .nav-item { display: block; text-decoration: none; }
        .nav-item-inner {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px;
            border-radius: 8px; color: #94a3b8; font-size: 14px; font-weight: 500;
            transition: all .15s ease; cursor: pointer;
        }
        .nav-item-inner:hover { background: rgba(255,255,255,.06); color: #fff; }
        .nav-item.active .nav-item-inner { background: var(--accent); color: #fff; box-shadow: 0 4px 12px rgba(59,130,246,.35); }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }
        .nav-badge {
            margin-left: auto; background: var(--danger); color: #fff;
            font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 999px; min-width: 18px; text-align: center;
        }

        .sidebar-footer {
            padding: 16px 12px; border-top: 1px solid var(--sidebar-border);
        }
        .admin-profile { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; }
        .admin-avatar {
            width: 34px; height: 34px; background: var(--accent); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 13px;
        }
        .admin-name { font-size: 13px; font-weight: 600; color: #fff; }
        .admin-role { font-size: 11px; color: var(--gray-400); }
        .btn-logout {
            display: flex; align-items: center; gap: 8px; padding: 8px 12px; width: 100%;
            background: transparent; border: 1px solid var(--sidebar-border); border-radius: 8px;
            color: var(--gray-400); font-size: 13px; cursor: pointer; margin-top: 8px;
            transition: all .15s ease; font-family: inherit;
        }
        .btn-logout:hover { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.3); color: #f87171; }

        /* ── Main Content ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        .topbar {
            background: #fff; border-bottom: 1px solid var(--gray-200); padding: 0 28px;
            height: 64px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow);
        }
        .topbar-left { display: flex; flex-direction: column; }
        .topbar-title { font-size: 16px; font-weight: 700; color: var(--text); }
        .topbar-breadcrumb { font-size: 12px; color: var(--gray-400); margin-top: 1px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-notif-btn {
            position: relative; background: none; border: none; cursor: pointer;
            width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
            color: var(--gray-600); font-size: 18px; transition: background .15s;
        }
        .topbar-notif-btn:hover { background: var(--gray-100); }
        .notif-dot {
            position: absolute; top: 6px; right: 6px; width: 8px; height: 8px;
            background: var(--danger); border-radius: 50%; border: 2px solid #fff;
        }

        .content-area { padding: 28px; flex: 1; }

        /* ── Toast Flash ── */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast {
            padding: 12px 18px; border-radius: 10px; font-size: 14px; font-weight: 500;
            min-width: 280px; max-width: 380px; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12); animation: slideIn .3s ease;
        }
        .toast-success { background: #ecfdf5; color: #065f46; border-left: 4px solid var(--success); }
        .toast-error { background: #fef2f2; color: #991b1b; border-left: 4px solid var(--danger); }
        .toast-icon { font-size: 16px; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { opacity: 1; } to { opacity: 0; transform: translateX(100%); } }

        /* ── Utilities ── */
        .card { background: #fff; border-radius: var(--radius); border: 1px solid var(--gray-200); box-shadow: var(--shadow); }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-body { padding: 24px; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
            border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: all .15s ease; font-family: inherit;
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(59,130,246,.3); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-danger { background: #fee2e2; color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        .btn-success { background: #d1fae5; color: #065f46; }
        .btn-success:hover { background: var(--success); color: #fff; }
        .btn-warning { background: #fef3c7; color: #92400e; }
        .btn-warning:hover { background: var(--warning); color: #fff; }
        .btn-sm { padding: 5px 10px; font-size: 12px; border-radius: 6px; }

        .badge {
            display: inline-flex; align-items: center; padding: 3px 8px;
            border-radius: 999px; font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: var(--gray-100); color: var(--gray-600); }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead th { background: var(--gray-50); color: var(--gray-600); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; padding: 10px 16px; border-bottom: 1px solid var(--gray-200); text-align: left; }
        tbody td { padding: 12px 16px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; color: var(--text); }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: var(--gray-50); }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 9px 12px; border: 1.5px solid var(--gray-200); border-radius: 8px;
            font-size: 13.5px; color: var(--text); font-family: inherit; transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        .form-hint { font-size: 12px; color: var(--gray-400); margin-top: 4px; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200;
            align-items: center; justify-content: center; backdrop-filter: blur(2px);
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 14px; width: 100%; max-width: 520px;
            max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
            animation: modalIn .2s ease;
        }
        @keyframes modalIn { from { transform: scale(.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
        .modal-title { font-size: 16px; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-400); line-height: 1; }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 10px; }

        /* Pagination */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--gray-200); }
        .pagination-info { font-size: 13px; color: var(--gray-400); }
        .pagination { display: flex; gap: 4px; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 6px; font-size: 13px; text-decoration: none;
        }
        .pagination a { color: var(--gray-700); border: 1px solid var(--gray-200); }
        .pagination a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
        .pagination span.active { background: var(--accent); color: #fff; border: 1px solid var(--accent); }
        .pagination span.disabled { color: var(--gray-200); border: 1px solid var(--gray-100); cursor: default; }

        /* Filter bar */
        .filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .filter-bar .form-control { width: auto; }
        .filter-bar select { padding: 8px 28px 8px 10px; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; background-size: 14px; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--gray-400); }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('admin.dashboard') }}">
            <div class="logo-icon">🏠</div>
            <div>
                <div class="logo-text">RentStuff</div>
                <div class="logo-sub">Admin Panel</div>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Utama</div>

        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">📊</span> Dashboard
            </div>
        </a>

        <div class="nav-section-title">Manajemen</div>

        <a href="{{ route('admin.categories.index') }}" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">🏷️</span> Kategori Barang
            </div>
        </a>

        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">👥</span> Pengguna
                @php $unverified = \App\Models\User::where('is_verified', false)->where('is_active', true)->count() @endphp
                @if($unverified > 0)
                    <span class="nav-badge">{{ $unverified }}</span>
                @endif
            </div>
        </a>

        <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">📋</span> Monitoring Transaksi
            </div>
        </a>

        <a href="{{ route('admin.disputes.index') }}" class="nav-item {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">⚖️</span> Penanganan Dispute
                @php $openDisputes = \App\Models\Dispute::where('status', 'open')->count() @endphp
                @if($openDisputes > 0)
                    <span class="nav-badge">{{ $openDisputes }}</span>
                @endif
            </div>
        </a>

        <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <div class="nav-item-inner">
                <span class="nav-icon">🔔</span> Notifikasi Sistem
                @php
                    $notifCount = \App\Models\Booking::where('status','pending')->count()
                        + \App\Models\User::where('is_verified',false)->where('is_active',true)->count()
                        + \App\Models\Dispute::where('status','open')->count();
                @endphp
                @if($notifCount > 0)
                    <span class="nav-badge">{{ $notifCount }}</span>
                @endif
            </div>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-profile">
            <div class="admin-avatar">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</div>
            <div>
                <div class="admin-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Logout</button>
        </form>
    </div>
</aside>

<!-- ── Main ── -->
<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-breadcrumb">RentStuff Admin › @yield('page-title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('admin.notifications.index') }}" class="topbar-notif-btn">
                🔔
                @if(isset($notifCount) && $notifCount > 0)
                    <span class="notif-dot"></span>
                @elseif(\App\Models\Booking::where('status','pending')->count() + \App\Models\User::where('is_verified',false)->where('is_active',true)->count() + \App\Models\Dispute::where('status','open')->count() > 0)
                    <span class="notif-dot"></span>
                @endif
            </a>
        </div>
    </header>

    <div class="content-area">
        @yield('content')
    </div>
</div>

<!-- ── Toast Flash ── -->
<div class="toast-container" id="toastContainer">
    @if(session('success'))
        <div class="toast toast-success">
            <span class="toast-icon">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="toast toast-error">
            <span class="toast-icon">❌</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>

<script>
    // Auto-dismiss toasts
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => {
            t.style.animation = 'slideOut .3s ease forwards';
            setTimeout(() => t.remove(), 300);
        }, 4000);
    });

    // Modal helpers
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    });
</script>
@stack('scripts')
</body>
</html>
