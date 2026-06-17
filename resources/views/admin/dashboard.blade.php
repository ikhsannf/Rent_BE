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
            <div style="font-size:11px;color:#3b82f6;margin-top:2px;">+{{ $stats['new_users_this_month'] }} bulan ini</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#d1fae5;display:flex;align-items:center;justify-content:center;font-size:22px;">📋</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">{{ number_format($stats['total_bookings']) }}</div>
            <div style="font-size:12px;color:#64748b;">Total Transaksi</div>
            <div style="font-size:11px;color:#10b981;margin-top:2px;">{{ $stats['ongoing_bookings'] }} sedang berjalan</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:22px;">💰</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">Rp {{ number_format($stats['total_revenue'],0,',','.') }}</div>
            <div style="font-size:12px;color:#64748b;">Platform Fee</div>
            <div style="font-size:11px;color:#f59e0b;margin-top:2px;">Dari transaksi selesai</div>
        </div>
    </div>

    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:22px;">⚖️</div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#1e293b;">{{ $stats['open_disputes'] }}</div>
            <div style="font-size:12px;color:#64748b;">Dispute Terbuka</div>
            <div style="font-size:11px;color:#ef4444;margin-top:2px;">Perlu ditangani</div>
        </div>
    </div>

</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Recent Bookings --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Transaksi Terbaru</span>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <table>
            <thead><tr>
                <th>Kode</th><th>Borrower</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse($recentBookings as $b)
                <tr>
                    <td><a href="{{ route('admin.bookings.show', $b) }}" style="color:#3b82f6;text-decoration:none;font-weight:600;">{{ $b->booking_code }}</a></td>
                    <td>{{ $b->borrower?->name ?? '-' }}</td>
                    <td>
                        @php
                            $sc = ['pending'=>'warning','approved'=>'info','ongoing'=>'info','completed'=>'success','rejected'=>'danger','cancelled'=>'gray','disputed'=>'danger'];
                        @endphp
                        <span class="badge badge-{{ $sc[$b->status] ?? 'gray' }}">{{ ucfirst($b->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:30px;">Belum ada transaksi</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Open Disputes --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚖️ Dispute Terbuka</span>
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <table>
            <thead><tr>
                <th>Pelapor</th><th>Alasan</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse($openDisputes as $d)
                <tr>
                    <td>{{ $d->reportedBy?->name ?? '-' }}</td>
                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $d->reason }}">{{ $d->reason }}</td>
                    <td><span class="badge badge-danger">Open</span></td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:30px;">Tidak ada dispute terbuka</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection