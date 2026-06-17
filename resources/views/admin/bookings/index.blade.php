@extends('admin.layouts.app')
@section('page-title', 'Monitoring Transaksi')

@section('content')

{{-- Filter --}}
<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="🔍 Kode booking / nama borrower..." value="{{ request('search') }}" style="min-width:260px;">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            @foreach(['pending','approved','ongoing','completed','rejected','cancelled','disputed'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Semua Transaksi</span>
        <span style="font-size:13px;color:#64748b;">{{ $bookings->total() }} transaksi</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Booking</th>
                <th>Barang</th>
                <th>Borrower</th>
                <th>Lender</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($bookings as $booking)
            @php
                $sc = ['pending'=>'warning','approved'=>'info','ongoing'=>'info','completed'=>'success','rejected'=>'danger','cancelled'=>'gray','disputed'=>'danger'];
                $pc = ['unpaid'=>'danger','paid'=>'success','refunded'=>'warning'];
            @endphp
            <tr>
                <td>
                    <span style="font-family:monospace;font-weight:700;font-size:13px;color:#3b82f6;">{{ $booking->booking_code }}</span>
                </td>
                <td style="max-width:160px;">
                    <div style="font-weight:500;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $booking->listing?->title ?? '-' }}</div>
                </td>
                <td style="font-size:13px;">{{ $booking->borrower?->name ?? '-' }}</td>
                <td style="font-size:13px;">{{ $booking->lender?->name ?? '-' }}</td>
                <td style="font-size:12px;color:#64748b;">
                    {{ $booking->start_date?->format('d M') }} – {{ $booking->end_date?->format('d M Y') }}
                </td>
                <td style="font-weight:600;font-size:13px;">Rp {{ number_format($booking->total_price,0,',','.') }}</td>
                <td><span class="badge badge-{{ $pc[$booking->payment_status] ?? 'gray' }}">{{ ucfirst($booking->payment_status) }}</span></td>
                <td><span class="badge badge-{{ $sc[$booking->status] ?? 'gray' }}">{{ ucfirst($booking->status) }}</span></td>
                <td>
                    <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-secondary btn-sm">👁️ Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>Belum ada transaksi.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>

    @if($bookings->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} dari {{ $bookings->total() }}</div>
        <div class="pagination">
            @if($bookings->onFirstPage()) <span class="disabled">‹</span> @else <a href="{{ $bookings->previousPageUrl() }}">‹</a> @endif
            @foreach($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                @if($page == $bookings->currentPage()) <span class="active">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($bookings->hasMorePages()) <a href="{{ $bookings->nextPageUrl() }}">›</a> @else <span class="disabled">›</span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
