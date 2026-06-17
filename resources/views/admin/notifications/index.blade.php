@extends('admin.layouts.app')
@section('page-title', 'Notifikasi Sistem')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="font-size:14px;color:#64748b;">Total <strong>{{ $totalCount }}</strong> notifikasi aktif yang perlu ditindaklanjuti</div>
    @if($totalCount == 0)
        <span class="badge badge-success" style="font-size:13px;padding:6px 14px;">✅ Semua Bersih</span>
    @else
        <span class="badge badge-danger" style="font-size:13px;padding:6px 14px;">🔔 {{ $totalCount }} Perlu Perhatian</span>
    @endif
</div>

<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Pengguna Belum Diverifikasi --}}
    @if($unverifiedUsers->count() > 0)
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:18px;">👥</div>
                <div>
                    <div class="card-title">Pengguna Menunggu Verifikasi</div>
                    <div style="font-size:12px;color:#94a3b8;">{{ $unverifiedUsers->count() }} akun perlu diverifikasi</div>
                </div>
            </div>
            <a href="{{ route('admin.users.index', ['status'=>'unverified']) }}" class="btn btn-warning btn-sm">Lihat Semua</a>
        </div>
        <div>
        @foreach($unverifiedUsers as $user)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-bottom:1px solid #f1f5f9;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#f59e0b;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:13px;">{{ substr($user->name,0,1) }}</div>
                    <div>
                        <div style="font-weight:600;font-size:14px;">{{ $user->name }}</div>
                        <div style="font-size:12px;color:#64748b;">{{ $user->email }} · {{ ucfirst($user->role) }} · Daftar {{ $user->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">✅ Verifikasi</button>
                </form>
            </div>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Booking Pending --}}
    @if($pendingBookings->count() > 0)
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:18px;">📋</div>
                <div>
                    <div class="card-title">Booking Menunggu Konfirmasi</div>
                    <div style="font-size:12px;color:#94a3b8;">{{ $pendingBookings->count() }} booking belum dikonfirmasi lender</div>
                </div>
            </div>
            <a href="{{ route('admin.bookings.index', ['status'=>'pending']) }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div>
        @foreach($pendingBookings as $booking)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-bottom:1px solid #f1f5f9;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:18px;">📦</div>
                    <div>
                        <div style="font-weight:700;font-size:13px;font-family:monospace;color:#3b82f6;">{{ $booking->booking_code }}</div>
                        <div style="font-size:12px;color:#64748b;">
                            {{ $booking->listing?->title ?? '-' }} · Borrower: {{ $booking->borrower?->name ?? '-' }} · {{ $booking->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-secondary btn-sm">👁️ Detail</a>
            </div>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Open Disputes --}}
    @if($openDisputes->count() > 0)
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:18px;">⚖️</div>
                <div>
                    <div class="card-title">Dispute Membutuhkan Perhatian</div>
                    <div style="font-size:12px;color:#94a3b8;">{{ $openDisputes->count() }} dispute belum ditangani</div>
                </div>
            </div>
            <a href="{{ route('admin.disputes.index', ['status'=>'open']) }}" class="btn btn-danger btn-sm">Lihat Semua</a>
        </div>
        <div>
        @foreach($openDisputes as $dispute)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-bottom:1px solid #f1f5f9;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:18px;">⚠️</div>
                    <div>
                        <div style="font-weight:600;font-size:14px;">{{ $dispute->reason }}</div>
                        <div style="font-size:12px;color:#64748b;">
                            Dilaporkan oleh {{ $dispute->reportedBy?->name ?? '-' }} · Terlapor: {{ $dispute->reportedUser?->name ?? '-' }} · {{ $dispute->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-danger btn-sm">⚖️ Tangani</a>
            </div>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Semua Bersih --}}
    @if($totalCount == 0)
    <div class="card" style="padding:60px 20px;text-align:center;">
        <div style="font-size:56px;margin-bottom:16px;">🎉</div>
        <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Semua Bersih!</div>
        <div style="font-size:14px;color:#64748b;">Tidak ada notifikasi yang memerlukan tindakan saat ini.</div>
    </div>
    @endif

</div>

@endsection
