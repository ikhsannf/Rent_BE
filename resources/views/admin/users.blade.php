@extends('admin.layouts.app')
@section('page-title', 'Pengguna')

@section('content')

<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama / email..." value="{{ $search ?? '' }}" style="min-width:240px;">
        <select name="role" class="form-control">
            <option value="">Semua Role</option>
            <option value="borrower" {{ ($role ?? '')=='borrower' ? 'selected' : '' }}>Borrower</option>
            <option value="lender" {{ ($role ?? '')=='lender' ? 'selected' : '' }}>Lender</option>
            <option value="admin" {{ ($role ?? '')=='admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">👥 Daftar Pengguna</span>
        <span style="font-size:13px;color:#64748b;">{{ count($users) }} pengguna ditemukan</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Telepon</th>
                <th>Bergabung</th>
                <th style="width:120px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:{{ ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'][abs(crc32($user['name'] ?? 'A')) % 5] }};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;">
                            {{ strtoupper(substr($user['name'] ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:14px;">{{ $user['name'] ?? 'Tanpa Nama' }}</div>
                            <div style="font-size:12px;color:#64748b;">{{ $user['email'] ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @php $rc = ['borrower'=>'info','lender'=>'success','admin'=>'purple'] @endphp
                    <span class="badge badge-{{ $rc[$user['role'] ?? ''] ?? 'gray' }}">{{ ucfirst($user['role'] ?? 'Unknown') }}</span>
                </td>
                <td style="font-size:13px;color:#64748b;">{{ $user['phone'] ?? '-' }}</td>
                <td style="font-size:12px;color:#94a3b8;">
                    {{ isset($user['createdAt']) ? \Carbon\Carbon::parse($user['createdAt'])->format('d M Y') : '-' }}
                </td>
                <td>
                    <a href="{{ route('admin.users.show', $user['id']) }}" class="btn btn-primary btn-sm">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <p>Tidak ada pengguna ditemukan.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
