@extends('layouts.portal')
@section('title', 'Keluar Masuk Siswa')
@section('content')
<header class="page-heading"><h1>Keluar Masuk Siswa</h1></header>

<section class="panel">
    <h2>Verifikasi izin siswa (disetujui)</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Nama siswa</th><th>Kelas</th><th>Waktu izin</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($approved as $item)
                <tr>
                    <td>{{ $item->student->name }}</td>
                    <td>{{ $item->student->kelas }}</td>
                    <td>{{ $item->leave_date->format('d M Y') }} · {{ substr($item->leave_time, 0, 5) }}–{{ substr($item->return_time, 0, 5) }}</td>
                    <td>
                        <form method="post" action="{{ route('teacher.exit', $item) }}" data-movement-form data-kind="exit" data-student="{{ $item->student->name }}">
                            @csrf
                            <input type="hidden" name="actual_exit_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                            <input type="hidden" name="exit_note">
                            <button type="button" class="button button--small" data-movement-open>Catat keluar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada siswa yang menunggu dicatat keluar.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</section>

<section class="panel">
    <h2>Transaksi hari ini</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Nama siswa</th><th>Waktu keluar</th><th>Waktu kembali</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($transactions as $item)
                <tr>
                    <td>{{ $item->student->name }}</td>
                    <td>{{ $item->actual_exit_at->format('H:i') }}</td>
                    <td>{{ $item->actual_return_at?->format('H:i') ?? '—' }}</td>
                    <td><span class="badge {{ $item->actual_return_at ? 'status-selesai' : 'status-keluar' }}">{{ $item->actual_return_at ? 'Selesai' : 'Keluar' }}</span></td>
                    <td>
                        @if(!$item->actual_return_at)
                            <form method="post" action="{{ route('teacher.return', $item) }}" data-movement-form data-kind="return" data-student="{{ $item->student->name }}" data-exit-at="{{ $item->actual_exit_at->format('d M Y H:i') }}">
                                @csrf
                                <input type="hidden" name="actual_return_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                                <input type="hidden" name="return_note">
                                <button type="button" class="button button--success button--small" data-movement-open>Catat kembali</button>
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada transaksi hari ini.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</section>

<div class="modal-backdrop" id="movement-modal" hidden role="presentation">
    <section class="modal modal--compact" role="dialog" aria-modal="true" aria-labelledby="movement-title" aria-describedby="movement-description" tabindex="-1">
        <div class="modal-icon modal-icon--info" aria-hidden="true">i</div>
        <h2 id="movement-title">Catat waktu</h2>
        <p id="movement-description"></p>
        <label for="movement-time">Waktu</label>
        <input id="movement-time" type="datetime-local" required>
        <label for="movement-note" style="margin-top:12px">Keterangan (opsional)</label>
        <textarea id="movement-note" class="modal-textarea" maxlength="1000"></textarea>
        <div class="modal-actions">
            <button type="button" class="button button--success" data-movement-confirm>Simpan</button>
            <button type="button" class="button button--outline" data-movement-cancel>Batal</button>
        </div>
    </section>
</div>
@endsection
