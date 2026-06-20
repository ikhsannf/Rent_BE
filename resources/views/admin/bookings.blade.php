@extends('admin.layouts.app')
@section('page-title', 'Transaksi')

@section('content')

<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari barang..." value="{{ $search ?? '' }}" style="min-width:240px;">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <option value="Menunggu" {{ ($status ?? '') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="Disewa" {{ ($status ?? '') == 'Disewa' ? 'selected' : '' }}>Disewa</option>
            <option value="Selesai" {{ ($status ?? '') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="Dibatalkan" {{ ($status ?? '') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.bookings') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Daftar Transaksi</span>
        <span style="font-size:13px;color:#64748b;">{{ count($bookings) }} transaksi ditemukan</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th>Penyewa</th>
                <th>Pemilik</th>
                <th>Tanggal Sewa</th>
                <th>Durasi</th>
                <th>Total</th>
                <th>Status</th>
                <th style="width:130px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($bookings as $b)
            @php
                $borrower = isset($b['borrowerId']) && isset($users[$b['borrowerId']]) ? $users[$b['borrowerId']] : null;
                $lender = isset($b['lenderId']) && isset($users[$b['lenderId']]) ? $users[$b['lenderId']] : null;
                $listingFS = isset($b['listingId']) && isset($listingsMap[$b['listingId']]) ? $listingsMap[$b['listingId']] : null;
            @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        @if(!empty($b['listingPhoto']))
                            <img src="{{ $b['listingPhoto'] }}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid var(--gray-200);" onerror="this.style.display='none'">
                        @endif
                        <div style="font-weight:500;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $b['listingTitle'] ?? $listingFS['title'] ?? $b['listingId'] ?? '-' }}
                        </div>
                    </div>
                </td>
                <td>
                    @if($borrower)
                        <div style="font-weight:500;font-size:13px;">{{ $borrower['name'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $borrower['email'] }}</div>
                    @else
                        <span style="font-size:12px;color:#94a3b8;">{{ $b['borrowerId'] ?? '-' }}</span>
                    @endif
                </td>
                <td>
                    @if($lender)
                        <div style="font-weight:500;font-size:13px;">{{ $lender['name'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $lender['email'] }}</div>
                    @else
                        <span style="font-size:12px;color:#94a3b8;">{{ $b['lenderId'] ?? '-' }}</span>
                    @endif
                </td>
                <td style="font-size:12px;color:#64748b;">
                    @if(isset($b['startDate']))
                        {{ \Carbon\Carbon::parse($b['startDate'])->format('d M') }}
                        @if(isset($b['endDate']))
                            — {{ \Carbon\Carbon::parse($b['endDate'])->format('d M Y') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td style="text-align:center;">
                    <span class="badge badge-info">{{ $b['durationDays'] ?? '-' }} hari</span>
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
                              (in_array($s, ['dibatalkan','cancelled','rejected']) ? 'danger' : 'gray')));
                    @endphp
                    <span class="badge badge-{{ $sc }}">{{ $b['status'] ?? 'Unknown' }}</span>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="openModal('statusModal-{{ $loop->index }}')">✏️ Status</button>

                    <div class="modal-overlay" id="statusModal-{{ $loop->index }}">
                        <div class="modal">
                            <div class="modal-header">
                                <span class="modal-title">Ubah Status Transaksi</span>
                                <button class="modal-close" onclick="closeModal('statusModal-{{ $loop->index }}')">✕</button>
                            </div>
                            <form method="POST" action="{{ route('admin.bookings.update', $b['id']) }}">
                                @csrf @method('PATCH')
                                <div class="modal-body">
                                    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">
                                        <strong>{{ $b['listingTitle'] ?? 'Barang' }}</strong>
                                        — Rp {{ isset($b['totalPrice']) ? number_format((float)str_replace(['.',','],'',$b['totalPrice']),0,',','.') : '-' }}
                                    </p>
                                    <div class="form-group">
                                        <label class="form-label">Status Baru</label>
                                        <select name="status" class="form-control">
                                            <option value="Menunggu" {{ $b['status'] == 'Menunggu' ? 'selected' : '' }}>⏳ Menunggu</option>
                                            <option value="Disewa" {{ $b['status'] == 'Disewa' ? 'selected' : '' }}>🔵 Disewa</option>
                                            <option value="Selesai" {{ $b['status'] == 'Selesai' ? 'selected' : '' }}>✅ Selesai</option>
                                            <option value="Dibatalkan" {{ $b['status'] == 'Dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal-{{ $loop->index }}')">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>Tidak ada transaksi ditemukan.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
