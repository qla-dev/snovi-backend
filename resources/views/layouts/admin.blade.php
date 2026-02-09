<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SNOVI CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        body { background: #0f172a; color: #e5e7eb; }
        a { text-decoration: none; }
        .card { background: #111827; border-color: #1f2937; }
        .form-control, .form-select { background: #0b1220; color: #e5e7eb; border-color: #1f2937; }
        .form-control:focus, .form-select:focus { background: #0b1220; color: #fff; border-color: #8b5cf6; box-shadow: none; }
        .form-control[type="file"] {
            background: #0b1220;
            color: #e5e7eb;
        }
        .form-control[type="file"]::file-selector-button {
            color: #0b1220;
            background: #8b5cf6;
            border: 1px solid #8b5cf6;
            border-radius: 8px;
            padding: 6px 12px;
            margin-right: 10px;
        }
        .form-control[type="file"]::file-selector-button:hover {
            background: #a855f7;
            border-color: #a855f7;
            color: #0b1220;
        }
        label, .form-label { color: #e5e7eb; }
        .form-control::placeholder,
        .form-select::placeholder { color: #cbd5e1; opacity: 0.9; }
        .form-control:-ms-input-placeholder { color: #cbd5e1; }
        .form-control::-ms-input-placeholder { color: #cbd5e1; }
        .table { color: #e5e7eb; }
        .table > :not(caption) > * > * { background: transparent; border-bottom-color: #1f2937; color: #e5e7eb; }
        .table tbody tr:hover { background: #1f2937; }
        .table thead th { color: #f8fafc; border-bottom-color: #2d3748; }
        .table td { color: #e5e7eb; }
        .text-muted { color: #9ca3af !important; }
        .nav-link.active { color: #a855f7 !important; font-weight: 600; }
        .badge-muted { background: #1f2937; color: #9ca3af; }
        /* Purple button scheme */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .btn-primary,
        .btn-outline-secondary {
            color: #fff;
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover,
        .btn-outline-secondary:hover {
            color: #0b1220;
            background-color: #a855f7;
            border-color: #a855f7;
        }
        .btn-primary:focus,
        .btn-outline-secondary:focus {
            box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.35);
        }
        .btn-outline-secondary {
            color: #fff;
        }
        /* Outline light (Nazad) */
        .btn-outline-light {
            color: #cbd5e1;
            background-color: transparent;
            border-color: #8b5cf6;
        }
        .btn-outline-light:hover {
            color: #0b1220;
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-outline-light:focus {
            box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.35);
        }
        /* Outline danger in red */
        .btn-outline-danger {
            color: #ef4444;
            border-color: #ef4444;
            background-color: transparent;
        }
        .btn-outline-danger:hover {
            color: #0b1220;
            background-color: #ef4444;
            border-color: #ef4444;
        }
        .btn-outline-danger:focus {
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.35);
        }
        /* Upload status bar */
        .upload-status-bar {
            position: fixed;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid #1f2937;
            color: #e5e7eb;
            padding: 10px 14px;
            border-radius: 14px;
            display: none;
            box-shadow: 0 10px 35px rgba(0,0,0,0.45);
            z-index: 1050;
        }
        .upload-status-bar.show { display: flex; gap: 14px; align-items: center; }
        .upload-pill {
            padding: 6px 10px;
            border-radius: 10px;
            background: #0b1220;
            border: 1px solid #1f2937;
            display: flex;
            gap: 6px;
            align-items: center;
            font-size: 14px;
        }
        .upload-dot {
            width: 10px; height: 10px;
            border-radius: 999px;
            background: #22c55e;
        }
        .upload-dot.pending { background: #f59e0b; }
        .upload-dot.uploading { background: #8b5cf6; animation: pulse 1s ease-in-out infinite; }
        .upload-dot.error { background: #ef4444; }
        .upload-dot.ready { background: #22c55e; }
        .upload-status-inline {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }
        /* Fixed header */
        nav.navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        /* DataTables theming */
        .dataTables_wrapper .dataTables_paginate .page-item .page-link {
            color: #e5e7eb;
            background: #111827;
            border: 1px solid #1f2937;
        }
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link,
        .dataTables_wrapper .dataTables_paginate .page-link.active {
            background: #8b5cf6;
            color: #0b1220;
            border-color: #8b5cf6;
        }
        .dataTables_wrapper .dataTables_paginate .page-item .page-link:hover {
            background: #1f2937;
            color: #fff;
        }
        .dataTables_wrapper .dataTables_info {
            color: #e5e7eb;
        }
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            color: #e5e7eb;
        }
        .dataTables_wrapper .dataTables_length select {
            color: #fff;
            background: #0b1220;
            border-color: #1f2937;
        }
        /* Force selects to be clickable */
        select, .form-select { cursor: pointer; }
        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }
        .toggle-row {
            margin: 0;
            padding: 0.75rem 0;
            border: 1px solid #1f2937;
            border-radius: 12px;
            background: #0b1220;
        }
    </style>
</head>
<body>
@php $isLogin = request()->routeIs('login') || request()->routeIs('login.post'); @endphp
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#111827;">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.stories.index') }}">SNOVI CMS</a>
        @unless($isLogin)
            <div class="navbar-nav me-auto">
                <a class="nav-link {{ request()->routeIs('admin.stories.*') ? 'active' : '' }}" href="{{ route('admin.stories.index') }}">Priče</a>
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Kategorije</a>
                <a class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}" href="{{ route('admin.subcategories.index') }}">Potkategorije</a>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="d-flex">
                @csrf
                <button class="btn btn-sm btn-outline-danger">Odjava</button>
            </form>
        @endunless
    </div>
</nav>

<div class="container py-4">
    @yield('content')
</div>

<div class="position-fixed bottom-0 start-50 translate-middle-x w-100" style="max-width:680px; z-index:1060; padding: 0 16px 16px;">
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tables = document.querySelectorAll('table.datatable');
        tables.forEach((tbl) => {
            const disableSortTargets = [];
            tbl.querySelectorAll('th').forEach((th, idx) => {
                const text = (th.textContent || '').trim().toLowerCase();
                if (text === 'akcije' || text === 'efekti') {
                    disableSortTargets.push(idx);
                }
            });
            new DataTable(tbl, {
                paging: true,
                searching: true,
                pageLength: 7,
                order: [],
                columnDefs: disableSortTargets.length
                    ? [{ orderable: false, targets: disableSortTargets }]
                    : [],
                language: {
                    search: 'Pretraga:',
                    lengthMenu: 'Prikaži _MENU_',
                    info: 'Prikaz _START_ - _END_ od _TOTAL_',
                    paginate: { previous: 'Prethodno', next: 'Sljedeće' },
                    infoEmpty: 'Nema podataka',
                    zeroRecords: 'Nema rezultata'
                }
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
