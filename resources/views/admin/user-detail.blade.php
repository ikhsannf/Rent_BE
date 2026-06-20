@extends('admin.layouts.app')
@section('page-title', 'Detail Pengguna — ' . ($user['name'] ?? 'Unknown'))

@section('content')

<div style="margin-bottom:16px;">
    <a href="{{ route('admin.users') }}" class="btn btn-secondary">← Kembali</a>
</div>

<div style="display:grid; grid-template-columns:1fr 2fr; gap:20px;">
    {{-- Profile Card --}}
    <div class="card" style="padding:24px; text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:{{ ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'][abs(crc32($user['name'] ?? 'A')) % 5] }};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:28px;margin:0 auto 12px;">
            {{ strtoupper(substr($user['name'] ?? '?', 0, 1)) }}
        </div>
        <h3 style="font-size:18px;font-weight:700;">{{ $user['name'] ?? 'Tanpa Nama' }}</h3>
        <div style="font-size:13px;color:#64748b;margin-bottom:12px;">{{ $user['email'] ?? '-' }}</div>
        @php $rc = ['borrower'=>'info','lender'=>'success','admin'=>'purple'] @endphp
        <span class="badge badge-{{ $rc[$user['role'] ?? ''] ?? 'gray' }}">{{ ucfirst($user['role'] ?? 'Unknown') }}</span>

        <hr style="border:none;border-top:1px solid var(--gray-200);margin:20px 0;">

        <div style="text-align:left;font-size:13px;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:#64748b;">📱 Telepon</span>
                <span style="font-weight:500;">{{ $user['phone'] ?? '-' }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:#64748b;">🆔 User ID</span>
                <span style="font-size:11px;max-width:140px;word-break:break-all;text-align:right;">{{ $user['id'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:#64748b;">📅 Dibuat</span>
                <span>{{ isset($user['createdAt']) ? \Carbon\Carbon::parse($user['createdAt'])->format('d M Y H:i') : '-' }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:#64748b;">🔄 Diupdate</span>
                <span>{{ isset($user['updated_at']) ? \Carbon\Carbon::parse($user['updated_at'])->format('d M Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Booking History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Riwayat Transaksi ({{ count($userBookings) }})</span>
        </div>
        <table>
            <thead><tr>
                <th>Barang</th><th>Tanggal</th><th>Total</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse($userBookings as $b)
                <tr>
                    <td style="font-weight:500;">{{ $b['listingTitle'] ?? $b['listingId'] ?? '-' }}</td>
                    <td style="font-size:12px;color:#94a3b8;">
                        {{ isset($b['startDate']) ? \Carbon\Carbon::parse($b['startDate'])->format('d M') : '-' }}
                        @if(isset($b['endDate']))
                            — {{ \Carbon\Carbon::parse($b['endDate'])->format('d M') }}
                        @endif
                    </td>
                    <td style="font-weight:600;">
                        Rp {{ isset($b['totalPrice']) ? number_format((float)str_replace(['.',','],'',$b['totalPrice']),0,',','.') : '-' }}
                    </td>
                    <td>
                        @php
                            $s = strtolower($b['status'] ?? '');
                            $sc = in_array($s, ['selesai','completed']) ? 'success' :
                                  (in_array($s, ['menunggu','pending']) ? 'warning' :
                                  (in_array($s, ['disewa','active','ongoing']) ? 'info' :
                                  (in_array($s, ['dibatalkan','cancelled']) ? 'danger' : 'gray')));
                        @endphp
                        <span class="badge badge-{{ $sc }}">{{ $b['status'] ?? 'Unknown' }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:30px;">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
