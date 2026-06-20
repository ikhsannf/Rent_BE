@extends('admin.layouts.app')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:16px; margin-bottom:24px;">

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:22px;">👥</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">{{ number_format($stats['total_users']) }}</div>
            <div style="font-size:12px;color:#64748b;">Total Pengguna</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#d1fae5;display:flex;align-items:center;justify-content:center;font-size:22px;">📦</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">{{ number_format($stats['total_listings']) }}</div>
            <div style="font-size:12px;color:#64748b;">Total Barang</div>
            <div style="font-size:11px;color:#10b981;margin-top:2px;">{{ $stats['active_listings'] }} tersedia</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:22px;">📋</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">{{ number_format($stats['total_bookings']) }}</div>
            <div style="font-size:12px;color:#64748b;">Total Transaksi</div>
            <div style="font-size:11px;color:#f59e0b;margin-top:2px;">{{ $stats['pending_bookings'] }} menunggu</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:22px;">💰</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">Rp {{ number_format($stats['total_revenue'],0,',','.') }}</div>
            <div style="font-size:12px;color:#64748b;">Estimasi Pendapatan</div>
        </div>
    </div>

</div>

{{-- Info Box --}}
<div class="card" style="padding:16px 20px;margin-bottom:20px;border-left:4px solid #3b82f6;">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:20px;">🔥</span>
        <div>
            <div style="font-weight:600;font-size:14px;">Terhubung ke Database</div>
            <div style="font-size:12px;color:#64748b;">
                Data admin diambil langsung dari project <strong>rentstuff-app</strong>.
                Perubahan dari aplikasi akan terlihat setelah refresh halaman.
            </div>
        </div>
    </div>
</div>

{{-- Recent Bookings --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Transaksi Terbaru</span>
        <a href="{{ route('admin.bookings') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>
    <table>
        <thead><tr>
            <th>Barang</th><th>Penyewa</th><th>Tanggal</th><th>Total</th><th>Status</th>
        </tr></thead>
        <tbody>
        @forelse($recentBookings as $b)
            <tr>
                <td style="font-weight:500;">{{ $b['listingTitle'] ?? $b['listingId'] ?? '-' }}</td>
                <td>{{ $b['borrowerId'] ?? '-' }}</td>
                <td style="font-size:12px;color:#94a3b8;">
                    {{ isset($b['createdAt']) ? \Carbon\Carbon::parse($b['createdAt'])->format('d M Y') : '-' }}
                </td>
                <td style="font-weight:600;">
                    Rp {{ isset($b['totalPrice']) ? number_format((float)str_replace(['.',','],'',$b['totalPrice']),0,',','.') : '-' }}
                </td>
                <td>
                    @php
                        $s = strtolower($b['status'] ?? '');
                        $sc = in_array($s, ['selesai','completed','done']) ? 'success' :
                              (in_array($s, ['menunggu','pending','waiting']) ? 'warning' :
                              (in_array($s, ['disewa','active','ongoing']) ? 'info' :
                              (in_array($s, ['dibatalkan','cancelled','rejected']) ? 'danger' : 'gray')));
                    @endphp
                    <span class="badge badge-{{ $sc }}">{{ $b['status'] ?? 'Unknown' }}</span>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:30px;">Belum ada transaksi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
