@extends('admin.layouts.app')
@section('page-title', 'Kategori Barang')

@section('content')

<div class="card">
    <div class="card-header">
        <span class="card-title">🏷️ Daftar Kategori Barang</span>
        <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Kategori</button>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Kategori</th>
                <th>Slug</th>
                <th>Deskripsi</th>
                <th>Sort</th>
                <th>Listing</th>
                <th>Status</th>
                <th style="width:160px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $cat)
            <tr>
                <td style="color:#94a3b8;font-size:12px;">{{ $cat->id }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $cat->color ?? '#e2e8f0' }};display:flex;align-items:center;justify-content:center;font-size:18px;">{{ $cat->icon ?? '📦' }}</div>
                        <div>
                            <div style="font-weight:600;font-size:14px;">{{ $cat->name }}</div>
                        </div>
                    </div>
                </td>
                <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $cat->slug }}</code></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b;font-size:13px;">{{ $cat->description ?? '-' }}</td>
                <td style="text-align:center;color:#64748b;">{{ $cat->sort_order }}</td>
                <td style="text-align:center;"><span class="badge badge-info">{{ $cat->listings_count }}</span></td>
                <td>
                    @if($cat->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-gray">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn btn-secondary btn-sm" onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->icon }}', '{{ $cat->color }}', '{{ addslashes($cat->description) }}', {{ $cat->sort_order }}, {{ $cat->is_active ? 1 : 0 }})">✏️ Edit</button>
                        @if($cat->listings_count == 0)
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                        @else
                        <span title="Tidak bisa hapus — masih ada listing" style="cursor:not-allowed;opacity:.4;" class="btn btn-danger btn-sm">🗑️</span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">
                <div class="empty-state">
                    <div class="empty-icon">🏷️</div>
                    <p>Belum ada kategori. Tambahkan kategori pertama!</p>
                </div>
            </td></tr>
        @endforelse
        </tbody>
    </table>

    @if($categories->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan {{ $categories->firstItem() }}–{{ $categories->lastItem() }} dari {{ $categories->total() }} kategori</div>
        <div class="pagination">
            @if($categories->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $categories->previousPageUrl() }}">‹</a>
            @endif
            @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                @if($page == $categories->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
            @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal Tambah --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">➕ Tambah Kategori</span>
            <button class="modal-close" onclick="closeModal('modalTambah')">✕</button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Elektronik" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji)</label>
                        <input type="text" name="icon" class="form-control" placeholder="📦" maxlength="10">
                        <span class="form-hint">Copy-paste emoji dari keyboard</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warna</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" name="color" value="#3b82f6" style="width:42px;height:38px;border:1.5px solid #e2e8f0;border-radius:8px;cursor:pointer;padding:2px;">
                            <input type="text" id="colorText" value="#3b82f6" class="form-control" style="flex:1;" placeholder="#3b82f6" maxlength="7" oninput="document.querySelector('[name=color]').value=this.value">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat kategori..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;">
                            <input type="checkbox" name="is_active" checked style="width:16px;height:16px;accent-color:#3b82f6;">
                            Aktif
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✏️ Edit Kategori</span>
            <button class="modal-close" onclick="closeModal('modalEdit')">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="editName" name="name" class="form-control" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji)</label>
                        <input type="text" id="editIcon" name="icon" class="form-control" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warna</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="editColor" name="color" style="width:42px;height:38px;border:1.5px solid #e2e8f0;border-radius:8px;cursor:pointer;padding:2px;">
                            <input type="text" id="editColorText" class="form-control" style="flex:1;" maxlength="7" oninput="document.getElementById('editColor').value=this.value">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea id="editDescription" name="description" class="form-control" rows="2"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" id="editSortOrder" name="sort_order" class="form-control" min="0">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;">
                            <input type="checkbox" id="editIsActive" name="is_active" style="width:16px;height:16px;accent-color:#3b82f6;">
                            Aktif
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary">Update Kategori</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(id, name, icon, color, desc, sort, isActive) {
    document.getElementById('editForm').action = `/admin/categories/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editIcon').value = icon || '';
    document.getElementById('editColor').value = color || '#3b82f6';
    document.getElementById('editColorText').value = color || '#3b82f6';
    document.getElementById('editDescription').value = desc || '';
    document.getElementById('editSortOrder').value = sort;
    document.getElementById('editIsActive').checked = isActive == 1;
    openModal('modalEdit');
}
// Sync color picker → text
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('[name=color]').addEventListener('input', function() {
        document.getElementById('colorText').value = this.value;
    });
    document.getElementById('editColor').addEventListener('input', function() {
        document.getElementById('editColorText').value = this.value;
    });
});
</script>
@endpush

@endsection
