@extends('admin.layouts.app')
@section('page-title', 'Detail Transaksi')

@section('content')

<div style="margin-bottom:16px;">
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">← Kembali</a>
</div>

@php
    $sc = ['pending'=>'warning','approved'=>'info','ongoing'=>'info','completed'=>'success','rejected'=>'danger','cancelled'=>'gray','disputed'=>'danger'];
    $pc = ['unpaid'=>'danger','paid'=>'success','refunded'=>'warning'];
@endphp

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

    {{-- Left: Main Info --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Header Card --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div>
                    <div style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Kode Booking</div>
                    <div style="font-size:22px;font-weight:800;font-family:monospace;color:#1e293b;">{{ $booking->booking_code }}</div>
                </div>
                <div style="text-align:right;">
                    <span class="badge badge-{{ $sc[$booking->status] ?? 'gray' }}" style="font-size:14px;padding:6px 14px;">{{ ucfirst($booking->status) }}</span>
                    <div style="margin-top:6px;"><span class="badge badge-{{ $pc[$booking->payment_status] ?? 'gray' }}">💳 {{ ucfirst($booking->payment_status) }}</span></div>
                </div>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
                <div><span style="color:#94a3b8;">Mulai</span><br><strong>{{ $booking->start_date?->format('d M Y') }}</strong></div>
                <div><span style="color:#94a3b8;">Selesai</span><br><strong>{{ $booking->end_date?->format('d M Y') }}</strong></div>
                <div><span style="color:#94a3b8;">Durasi</span><br><strong>{{ $booking->total_days }} hari</strong></div>
                <div><span style="color:#94a3b8;">Dibuat</span><br><strong>{{ $booking->created_at->format('d M Y H:i') }}</strong></div>
            </div>
        </div>

        {{-- Barang --}}
        <div class="card">
            <div class="card-header"><span class="card-title">📦 Informasi Barang</span></div>
            <div class="card-body">
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">{{ $booking->listing?->title ?? '-' }}</div>
                <div style="font-size:13px;color:#64748b;margin-bottom:12px;">{{ $booking->listing?->category?->name ?? '-' }}</div>
                @if($booking->notes)
                    <div style="background:#fffbeb;border:1px solid #fef3c7;border-radius:8px;padding:12px;font-size:13px;">
                        <strong style="color:#92400e;">📝 Catatan Borrower:</strong><br>{{ $booking->notes }}
                    </div>
                @endif
                @if($booking->rejection_reason)
                    <div style="background:#fef2f2;border:1px solid #fee2e2;border-radius:8px;padding:12px;font-size:13px;margin-top:10px;">
                        <strong style="color:#991b1b;">❌ Alasan Penolakan:</strong><br>{{ $booking->rejection_reason }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header"><span class="card-title">📅 Timeline Status</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:0;">
                    @php
                        $timeline = [
                            ['label'=>'Booking Dibuat','time'=>$booking->created_at,'icon'=>'📋','done'=>true],
                            ['label'=>'Disetujui','time'=>$booking->approved_at,'icon'=>'✅','done'=>!!$booking->approved_at],
                            ['label'=>'Dimulai','time'=>$booking->started_at,'icon'=>'🚀','done'=>!!$booking->started_at],
                            ['label'=>'Selesai','time'=>$booking->completed_at,'icon'=>'🏁','done'=>!!$booking->completed_at],
                            ['label'=>'Dibatalkan','time'=>$booking->cancelled_at,'icon'=>'❌','done'=>!!$booking->cancelled_at],
                        ];
                    @endphp
                    @foreach($timeline as $i => $step)
                        @if($step['time'] || $step['done'])
                        <div style="display:flex;gap:14px;padding-bottom:{{ !$loop->last ? '16px' : '0' }};position:relative;">
                            @if(!$loop->last)
                            <div style="position:absolute;left:15px;top:28px;width:2px;height:calc(100% - 12px);background:#e2e8f0;"></div>
                            @endif
                            <div style="width:30px;height:30px;border-radius:50%;background:{{ $step['done'] ? '#dbeafe' : '#f1f5f9' }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;z-index:1;">{{ $step['icon'] }}</div>
                            <div>
                                <div style="font-weight:600;font-size:13px;color:{{ $step['done'] ? '#1e293b' : '#94a3b8' }};">{{ $step['label'] }}</div>
                                @if($step['time'])
                                    <div style="font-size:12px;color:#94a3b8;">{{ $step['time']->format('d M Y, H:i') }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Finansial + Users --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Finansial --}}
        <div class="card">
            <div class="card-header"><span class="card-title">💰 Finansial</span></div>
            <div class="card-body" style="font-size:14px;">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Harga/Hari</span>
                    <strong>Rp {{ number_format($booking->price_per_day,0,',','.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Durasi</span>
                    <strong>{{ $booking->total_days }} hari</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Deposit</span>
                    <strong>Rp {{ number_format($booking->deposit_amount,0,',','.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b;">Total Harga</span>
                    <strong style="font-size:16px;">Rp {{ number_format($booking->total_price,0,',','.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#ef4444;">Platform Fee (5%)</span>
                    <strong style="color:#ef4444;">Rp {{ number_format($booking->platform_fee,0,',','.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span style="color:#10b981;">Pendapatan Lender</span>
                    <strong style="color:#10b981;">Rp {{ number_format($booking->lender_income,0,',','.') }}</strong>
                </div>
            </div>
        </div>

        {{-- Borrower --}}
        <div class="card">
            <div class="card-header"><span class="card-title">🙋 Borrower</span></div>
            <div class="card-body">
                @php $b = $booking->borrower; @endphp
                @if($b)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;">{{ substr($b->name,0,1) }}</div>
                    <div>
                        <div style="font-weight:700;">{{ $b->name }}</div>
                        <div style="font-size:12px;color:#64748b;">{{ $b->email }}</div>
                    </div>
                </div>
                <div style="font-size:13px;color:#64748b;">📱 {{ $b->phone ?? '-' }}</div>
                @endif
            </div>
        </div>

        {{-- Lender --}}
        <div class="card">
            <div class="card-header"><span class="card-title">🏪 Lender</span></div>
            <div class="card-body">
                @php $l = $booking->lender; @endphp
                @if($l)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;">{{ substr($l->name,0,1) }}</div>
                    <div>
                        <div style="font-weight:700;">{{ $l->name }}</div>
                        <div style="font-size:12px;color:#64748b;">{{ $l->email }}</div>
                    </div>
                </div>
                <div style="font-size:13px;color:#64748b;">📱 {{ $l->phone ?? '-' }}</div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
