@extends('admin.layouts.app')
@section('page-title', 'Penanganan Dispute')

@section('content')

{{-- Filter --}}
<div class="card" style="padding:16px 20px;margin-bottom:20px;">
    <form method="GET" class="filter-bar">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            @foreach(['open','investigating','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request('status'))
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">⚖️ Daftar Dispute</span>
        <span style="font-size:13px;color:#64748b;">{{ $disputes->total() }} dispute</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Booking</th>
                <th>Pelapor</th>
                <th>Terlapor</th>
                <th>Alasan</th>
                <th>Denda</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($disputes as $dispute)
            @php
                $ds = ['open'=>'danger','investigating'=>'warning','resolved'=>'success','closed'=>'gray'];
            @endphp
            <tr>
                <td style="color:#94a3b8;font-size:12px;">{{ $dispute->id }}</td>
                <td>
                    @if($dispute->booking)
                        <a href="{{ route('admin.bookings.show', $dispute->booking) }}" style="color:#3b82f6;text-decoration:none;font-family:monospace;font-weight:600;font-size:12px;">
                            {{ $dispute->booking->booking_code }}
                        </a>
                    @else
                        <span style="color:#94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $dispute->reportedBy?->name ?? '-' }}</div>
                    <div style="font-size:11px;color:#94a3b8;">{{ $dispute->reportedBy?->role ?? '' }}</div>
                </td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $dispute->reportedUser?->name ?? '-' }}</div>
                    <div style="font-size:11px;color:#94a3b8;">{{ $dispute->reportedUser?->role ?? '' }}</div>
                </td>
                <td style="max-width:180px;">
                    <div style="font-weight:500;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $dispute->reason }}">{{ $dispute->reason }}</div>
                </td>
                <td style="font-size:13px;">
                    @if($dispute->fine_amount > 0)
                        <span style="color:#ef4444;font-weight:700;">Rp {{ number_format($dispute->fine_amount,0,',','.') }}</span>
                    @else
                        <span style="color:#94a3b8;">-</span>
                    @endif
                </td>
                <td style="font-size:12px;color:#94a3b8;">{{ $dispute->created_at->format('d M Y') }}</td>
                <td><span class="badge badge-{{ $ds[$dispute->status] ?? 'gray' }}">{{ ucfirst($dispute->status) }}</span></td>
                <td>
                    <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-secondary btn-sm">👁️ Tinjau</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">
                <div class="empty-state">
                    <div class="empty-icon">⚖️</div>
                    <p>Tidak ada dispute yang ditemukan.</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>

    @if($disputes->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan {{ $disputes->firstItem() }}–{{ $disputes->lastItem() }} dari {{ $disputes->total() }}</div>
        <div class="pagination">
            @if($disputes->onFirstPage()) <span class="disabled">‹</span> @else <a href="{{ $disputes->previousPageUrl() }}">‹</a> @endif
            @foreach($disputes->getUrlRange(1, $disputes->lastPage()) as $page => $url)
                @if($page == $disputes->currentPage()) <span class="active">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($disputes->hasMorePages()) <a href="{{ $disputes->nextPageUrl() }}">›</a> @else <span class="disabled">›</span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
