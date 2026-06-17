@extends('admin.layouts.app')
@section('page-title', 'Detail Dispute')

@section('content')

<div style="margin-bottom:16px;">
    <a href="{{ route('admin.disputes.index') }}" class="btn btn-secondary">← Kembali</a>
</div>

@php
    $ds = ['open'=>'danger','investigating'=>'warning','resolved'=>'success','closed'=>'gray'];
@endphp

<div style="display:grid;grid-template-columns:3fr 2fr;gap:20px;align-items:start;">

    {{-- Left --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Header --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Dispute #{{ $dispute->id }}</div>
                    <div style="font-size:20px;font-weight:800;color:#1e293b;margin-top:4px;">{{ $dispute->reason }}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;">Dilaporkan {{ $dispute->created_at->diffForHumans() }}</div>
                </div>
                <span class="badge badge-{{ $ds[$dispute->status] ?? 'gray' }}" style="font-size:14px;padding:7px 16px;">{{ ucfirst($dispute->status) }}</span>
            </div>
        </div>

        {{-- Detail Laporan --}}
        <div class="card">
            <div class="card-header"><span class="card-title">📄 Detail Laporan</span></div>
            <div class="card-body">
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Deskripsi</div>
                    <div style="background:#f8fafc;border-radius:10px;padding:16px;font-size:14px;line-height:1.6;color:#334155;">{{ $dispute->description }}</div>
                </div>

                @if($dispute->booking)
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Booking Terkait</div>
                    <a href="{{ route('admin.bookings.show', $dispute->booking) }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;text-decoration:none;color:#1e40af;font-weight:600;font-size:13px;">
                        📋 {{ $dispute->booking->booking_code }}
                        <span style="font-weight:400;color:#3b82f6;">→ Lihat Detail</span>
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Pihak yang Terlibat --}}
        <div class="card">
            <div class="card-header"><span class="card-title">👥 Pihak yang Terlibat</span></div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                @foreach([['label'=>'Pelapor','user'=>$dispute->reportedBy,'color'=>'#3b82f6'],['label'=>'Terlapor','user'=>$dispute->reportedUser,'color'=>'#ef4444']] as $p)
                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">{{ $p['label'] }}</div>
                    @if($p['user'])
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:{{ $p['color'] }};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;">{{ substr($p['user']->name,0,1) }}</div>
                        <div>
                            <div style="font-weight:700;font-size:13px;">{{ $p['user']->name }}</div>
                            <div style="font-size:11px;color:#64748b;">{{ $p['user']->email }}</div>
                        </div>
                    </div>
                    <div style="font-size:12px;color:#64748b;">
                        Role: <strong>{{ ucfirst($p['user']->role) }}</strong><br>
                        ⭐ Rating: {{ $p['user']->rating ?? '0.0' }}
                    </div>
                    @else
                    <span style="color:#94a3b8;font-size:13px;">-</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Admin Notes (existing) --}}
        @if($dispute->admin_notes)
        <div class="card">
            <div class="card-header"><span class="card-title">📝 Catatan Admin</span></div>
            <div class="card-body">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;font-size:14px;line-height:1.6;">{{ $dispute->admin_notes }}</div>
                @if($dispute->resolvedBy)
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                    Ditangani oleh: <strong>{{ $dispute->resolvedBy->name }}</strong>
                    @if($dispute->resolved_at) pada {{ $dispute->resolved_at->format('d M Y H:i') }} @endif
                </div>
                @endif
                @if($dispute->fine_amount > 0)
                <div style="margin-top:10px;background:#fef2f2;border-radius:8px;padding:12px;font-size:14px;">
                    💰 Denda dikenakan: <strong style="color:#ef4444;">Rp {{ number_format($dispute->fine_amount,0,',','.') }}</strong>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Right: Resolve Form --}}
    <div>
        @if(!in_array($dispute->status, ['resolved','closed']))
        <div class="card">
            <div class="card-header"><span class="card-title">⚖️ Resolusi Admin</span></div>
            <div class="card-body">
                <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST">
                    @csrf @method('PATCH')

                    <div class="form-group">
                        <label class="form-label">Update Status <span style="color:#ef4444;">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">— Pilih Status —</option>
                            <option value="investigating" {{ $dispute->status=='investigating' ? 'selected' : '' }}>🔍 Investigating (Sedang Ditinjau)</option>
                            <option value="resolved">✅ Resolved (Terselesaikan)</option>
                            <option value="closed">🔒 Closed (Ditutup)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Admin <span style="color:#ef4444;">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="5" required
                            placeholder="Tuliskan keputusan admin, kondisi barang, tindakan yang diambil...">{{ $dispute->admin_notes }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Denda (Rp)</label>
                        <input type="number" name="fine_amount" class="form-control"
                            value="{{ $dispute->fine_amount ?? 0 }}" min="0" step="1000"
                            placeholder="0">
                        <span class="form-hint">Isi 0 jika tidak ada denda. Denda dikenakan kepada pihak yang bersalah.</span>
                    </div>

                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px;font-size:12px;margin-bottom:16px;">
                        ⚠️ Pastikan Anda sudah memeriksa semua bukti sebelum membuat keputusan. Keputusan ini akan tercatat dan tidak dapat dibatalkan.
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;" onclick="return confirm('Yakin ingin menyimpan keputusan ini?')">
                        💾 Simpan Keputusan
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card" style="padding:24px;text-align:center;">
            <div style="font-size:40px;margin-bottom:12px;">{{ $dispute->status === 'resolved' ? '✅' : '🔒' }}</div>
            <div style="font-weight:700;font-size:16px;margin-bottom:6px;">Dispute {{ ucfirst($dispute->status) }}</div>
            <div style="font-size:13px;color:#64748b;">Dispute ini sudah diselesaikan dan tidak bisa diubah lagi.</div>
        </div>
        @endif
    </div>

</div>

@endsection
