@extends('admin.layouts.app')
@section('page-title', 'Verifikasi Pengguna')

@section('content')

{{-- Filter Bar --}}
<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama / email..." value="{{ request('search') }}" style="min-width:240px;">
        <select name="role" class="form-control">
            <option value="">Semua Role</option>
            <option value="borrower" {{ request('role')=='borrower' ? 'selected' : '' }}>Borrower</option>
            <option value="lender" {{ request('role')=='lender' ? 'selected' : '' }}>Lender</option>
            <option value="admin" {{ request('role')=='admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <option value="unverified" {{ request('status')=='unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
            <option value="verified" {{ request('status')=='verified' ? 'selected' : '' }}>Terverifikasi</option>
            <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">👥 Daftar Pengguna</span>
        <span style="font-size:13px;color:#64748b;">{{ $users->total() }} pengguna ditemukan</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Telepon</th>
                <th>Bergabung</th>
                <th>Verifikasi</th>
                <th>Status</th>
                <th style="width:180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if($user->avatar)
                            <img src="{{ $user->avatar_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                        @else
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'][crc32($user->name) % 5] }};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:600;font-size:14px;">{{ $user->name }}</div>
                            <div style="font-size:12px;color:#64748b;">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @php $roleColors = ['borrower'=>'info','lender'=>'success','admin'=>'purple'] @endphp
                    <span class="badge badge-{{ $roleColors[$user->role] ?? 'gray' }}">{{ ucfirst($user->role) }}</span>
                </td>
                <td style="font-size:13px;color:#64748b;">{{ $user->phone ?? '-' }}</td>
                <td style="font-size:12px;color:#94a3b8;">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    @if($user->is_verified)
                        <span class="badge badge-success">✅ Terverifikasi</span>
                    @else
                        <span class="badge badge-warning">⏳ Belum</span>
                    @endif
                </td>
                <td>
                    @if($user->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @if(!$user->is_verified)
                            <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Verifikasi akun {{ $user->name }}?')">✅ Verifikasi</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-secondary' }} btn-sm"
                                onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}?')">
                                {{ $user->is_active ? '🚫 Nonaktifkan' : '✅ Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <p>Tidak ada pengguna yang ditemukan.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna</div>
        <div class="pagination">
            @if($users->onFirstPage()) <span class="disabled">‹</span> @else <a href="{{ $users->previousPageUrl() }}">‹</a> @endif
            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if($page == $users->currentPage()) <span class="active">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($users->hasMorePages()) <a href="{{ $users->nextPageUrl() }}">›</a> @else <span class="disabled">›</span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
