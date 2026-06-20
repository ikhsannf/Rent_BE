@extends('admin.layouts.app')
@section('page-title', 'Barang')

@section('content')

<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari barang..." value="{{ $search ?? '' }}" style="min-width:240px;">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <option value="aktif" {{ ($status ?? '') == 'aktif' ? 'selected' : '' }}>Tersedia</option>
            <option value="rented" {{ ($status ?? '') == 'rented' ? 'selected' : '' }}>Disewa</option>
            <option value="nonaktif" {{ ($status ?? '') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.listings') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📦 Daftar Barang</span>
        <span style="font-size:13px;color:#64748b;">{{ count($listings) }} barang ditemukan</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th>Kategori</th>
                <th>Harga/Hari</th>
                <th>Deposit</th>
                <th>Rating</th>
                <th>Status</th>
                <th style="width:80px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($listings as $listing)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if(!empty($listing['photos'][0]))
                            <img src="{{ $listing['photos'][0] }}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--gray-200);" onerror="this.style.display='none'">
                        @endif
                        <div>
                            <div style="font-weight:600;font-size:14px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $listing['title'] ?? 'Tanpa Judul' }}</div>
                            <div style="font-size:11px;color:#94a3b8;">{{ $listing['lenderName'] ?? $listing['lenderId'] ?? 'Pemilik tidak diketahui' }}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;color:#64748b;">
                    {{ $listing['categoryId'] ?? '-' }}
                </td>
                <td style="font-weight:600;">
                    Rp {{ isset($listing['pricePerDay']) ? number_format((float)str_replace(['.',','],'',$listing['pricePerDay']),0,',','.') : '-' }}
                </td>
                <td style="font-size:13px;color:#64748b;">
                    {{ isset($listing['deposit']) ? 'Rp '.number_format((float)str_replace(['.',','],'',$listing['deposit']),0,',','.') : '-' }}
                </td>
                <td>
                    <span style="color:#f59e0b;">★</span>
                    <span style="font-size:13px;">{{ $listing['averageRating'] ?? $listing['rating'] ?? '-' }}</span>
                    <span style="font-size:11px;color:#94a3b8;">({{ $listing['reviewCount'] ?? '0' }})</span>
                </td>
                <td>
                    @php
                        $s = strtolower($listing['status'] ?? '');
                        $sc = in_array($s, ['aktif','available']) ? 'success' :
                              (in_array($s, ['rented','disewa']) ? 'warning' :
                              (in_array($s, ['nonaktif','inactive']) ? 'danger' : 'gray'));
                    @endphp
                    <span class="badge badge-{{ $sc }}">{{ $listing['status'] ?? 'Unknown' }}</span>
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.listings.delete', $listing['id']) }}" onsubmit="return confirm('Hapus barang ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <p>Tidak ada barang ditemukan.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
