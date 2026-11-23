@extends('layouts.portal')
@section('title', 'Verifikasi Izin')
@section('content')
<header class="page-heading"><h1>Verifikasi Izin</h1></header>
<div class="content-grid">
    <section class="panel">
        <h2>Daftar pengajuan</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Nama siswa</th><th>Kelas</th><th>Jenis izin</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr @class(['is-selected' => $selected?->id === $item->id])>
                        <td><a href="{{ route('teacher.verification', ['selected' => $item->id]) }}">{{ $item->student->name }}</a></td>
                        <td>{{ $item->student->kelas }}</td><td>{{ $item->type }}</td>
                        <td><span class="badge status-menunggu">Menunggu</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4">Tidak ada pengajuan yang menunggu.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div style="margin-top:14px">{{ $requests->links() }}</div>
    </section>

    <section class="panel">
        <h2>Detail pengajuan</h2>
        @if($selected)
            <dl class="detail-list">
                <div><dt>Nama</dt><dd>{{ $selected->student->name }}</dd></div>
                <div><dt>Kelas</dt><dd>{{ $selected->student->kelas }}</dd></div>
                <div><dt>Jenis izin</dt><dd>{{ $selected->type }}</dd></div>
                <div><dt>Alasan</dt><dd>{{ $selected->reason }}</dd></div>
                <div><dt>Tujuan</dt><dd>{{ $selected->destination }}</dd></div>
                <div><dt>Waktu</dt><dd>{{ $selected->leave_date->format('d M Y') }} · {{ substr($selected->leave_time, 0, 5) }} – {{ $selected->return_date->format('d M Y') }} · {{ substr($selected->return_time, 0, 5) }}</dd></div>
                <div><dt>Status</dt><dd><span class="badge status-{{ $selected->status }}">{{ ucfirst($selected->status) }}</span></dd></div>
            </dl>
            @if($selected->evidence_path)
                <p><a target="_blank" rel="noopener" href="{{ route('leave.evidence', $selected) }}">Buka bukti lampiran</a></p>
            @endif
            @if($selected->status === 'menunggu')
                <div class="button-row" style="margin-top:18px">
                    <form method="post" action="{{ route('teacher.decide', $selected) }}" data-approval-form
                        data-student="{{ $selected->student->name }}" data-class="{{ $selected->student->kelas }}"
                        data-type="{{ $selected->type }}" data-destination="{{ $selected->destination }}"
                        data-date="{{ $selected->leave_date->format('d M Y') }}"
                        data-period="{{ substr($selected->leave_time, 0, 5) }}–{{ substr($selected->return_time, 0, 5) }}">
                        @csrf<input type="hidden" name="decision" value="disetujui">
                        <button type="submit" class="button--success">Setujui</button>
                    </form>
                    <form method="post" action="{{ route('teacher.decide', $selected) }}" data-rejection-form>
                        @csrf<input type="hidden" name="decision" value="ditolak">
                        <input type="hidden" name="rejection_reason">
                        <button type="submit" class="button--danger">Tolak</button>
                    </form>
                    <a class="button button--outline" href="{{ route('teacher.history', $selected) }}">Riwayat status</a>
                </div>
            @endif
        @else
            <p>Pilih pengajuan pada daftar untuk melihat rinciannya.</p>
        @endif
    </section>
</div>

@if($selected?->status === 'menunggu')
    <div class="modal-backdrop" id="approval-confirm" hidden role="presentation">
        <section class="modal modal--compact" role="alertdialog" aria-modal="true" aria-labelledby="approval-title" aria-describedby="approval-message" tabindex="-1">
            <div class="modal-icon modal-icon--info" aria-hidden="true">i</div>
            <h2 id="approval-title">Setujui izin?</h2>
            <p id="approval-message">Pastikan data pengajuan sudah benar. Notifikasi akan dikirim otomatis.</p>
            <div class="modal-summary"><dl>
                <div><dt>Siswa</dt><dd data-approval="student">—</dd></div>
                <div><dt>Kelas</dt><dd data-approval="class">—</dd></div>
                <div><dt>Jenis izin</dt><dd data-approval="type">—</dd></div>
                <div><dt>Tujuan</dt><dd data-approval="destination">—</dd></div>
                <div><dt>Waktu</dt><dd><span data-approval="date">—</span> · <span data-approval="period">—</span></dd></div>
            </dl></div>
            <div class="modal-actions">
                <button type="button" class="button button--success" data-approval-confirm>Setujui</button>
                <button type="button" class="button button--outline" data-approval-cancel>Batal</button>
            </div>
        </section>
    </div>

    <div class="modal-backdrop" id="rejection-confirm" hidden role="presentation">
        <section class="modal modal--compact" role="alertdialog" aria-modal="true" aria-labelledby="rejection-title" aria-describedby="rejection-message" tabindex="-1">
            <div class="modal-icon modal-icon--warning" aria-hidden="true">!</div>
            <h2 id="rejection-title">Tolak izin ini?</h2>
            <p id="rejection-message">Berikan alasan agar siswa mengetahui alasan pengajuan ditolak.</p>
            <label for="rejection-reason">Alasan penolakan (wajib)</label>
            <textarea id="rejection-reason" class="modal-textarea" maxlength="1000" placeholder="Tuliskan alasan penolakan"></textarea>
            <div class="modal-actions">
                <button type="button" class="button button--danger" data-rejection-confirm>Tolak</button>
                <button type="button" class="button button--outline" data-rejection-cancel>Batal</button>
            </div>
        </section>
    </div>
@endif
@endsection
