@extends('layouts.portal')
@section('title','Riwayat Pengajuan Izin')
@section('content')
<header class="page-heading"><h1>Riwayat Pengajuan Izin</h1><a class="button" href="{{ route('student.requests.create') }}">+ Ajukan Izin</a></header>
<form class="filter-bar" method="get" action="{{ route('student.requests.index') }}" data-history-filter>
    <div class="form-group"><label for="status">Filter status</label><select id="status" name="status">@foreach(['semua'=>'Semua status','menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','keluar'=>'Keluar','selesai'=>'Selesai'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['status'] ?? 'semua') === $key)>{{ $label }}</option>@endforeach</select></div>
</form>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>No</th><th>Tanggal</th><th>Jenis izin</th><th>Alasan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($requests as $item)<tr><td>{{ $requests->firstItem()+$loop->index }}</td><td>{{ $item->leave_date->format('d M Y') }}</td><td>{{ $item->type }}</td><td>{{ $item->reason }}</td><td><span class="badge status-{{ $item->status }}">{{ ucfirst($item->status) }}</span></td><td><div class="button-row"><button type="button" class="button button--outline button--small" data-request-detail data-type="{{ $item->type }}" data-reason="{{ $item->reason }}" data-destination="{{ $item->destination }}" data-date="{{ $item->leave_date->format('d M Y') }}" data-period="{{ $item->leave_date->format('d M Y') }} {{ substr($item->leave_time,0,5) }} – {{ $item->return_date->format('d M Y') }} {{ substr($item->return_time,0,5) }}" data-status="{{ ucfirst($item->status) }}">Detail</button>@if($item->status==='menunggu')<a class="button button--outline button--small" href="{{ route('student.requests.edit',$item) }}">Edit</a><form method="post" action="{{ route('student.requests.destroy',$item) }}" onsubmit="return confirm('Yakin ingin menghapus pengajuan izin ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('delete')<button class="button--danger button--small">Hapus</button></form>@endif</div></td></tr>@empty<tr><td colspan="6">Belum ada pengajuan yang cocok dengan filter.</td></tr>@endforelse</tbody></table></div><div style="margin-top:16px">{{ $requests->links() }}</div></section>
<div class="modal-backdrop" id="request-detail-modal" hidden role="presentation">
    <section class="modal modal--compact" role="dialog" aria-modal="true" aria-labelledby="request-detail-title" tabindex="-1">
        <div class="modal-icon modal-icon--info" aria-hidden="true">i</div>
        <h2 id="request-detail-title">Detail pengajuan</h2>
        <div class="modal-summary"><dl>
            <div><dt>Jenis izin</dt><dd data-detail="type">—</dd></div>
            <div><dt>Alasan</dt><dd data-detail="reason">—</dd></div>
            <div><dt>Tujuan</dt><dd data-detail="destination">—</dd></div>
            <div><dt>Waktu</dt><dd data-detail="period">—</dd></div>
            <div><dt>Status</dt><dd data-detail="status">—</dd></div>
        </dl></div>
        <div class="modal-actions"><button type="button" data-detail-close>Tutup</button></div>
    </section>
</div>
<div class="modal-backdrop" id="delete-confirm" hidden role="presentation">
    <section class="modal modal--compact" role="alertdialog" aria-modal="true" aria-labelledby="delete-confirm-title" aria-describedby="delete-confirm-message" tabindex="-1">
        <div class="modal-icon modal-icon--warning" aria-hidden="true">!</div>
        <h2 id="delete-confirm-title">Hapus pengajuan izin?</h2>
        <p id="delete-confirm-message">Pengajuan ini akan dihapus permanen dan tidak dapat dipulihkan.</p>
        <div class="modal-actions">
            <button type="button" class="button button--outline" data-delete-cancel>Batalkan</button>
            <button type="button" class="button button--danger" data-delete-confirm>Ya, hapus</button>
        </div>
    </section>
</div>
@endsection
