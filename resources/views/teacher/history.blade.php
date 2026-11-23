@extends('layouts.portal')
@section('title', 'Riwayat Status Izin')
@section('content')
<header class="page-heading">
    <h1>Riwayat Status Izin</h1>
    <a class="button button--outline" href="{{ route('teacher.verification', ['selected' => $leaveRequest->id]) }}">Kembali</a>
</header>

<section class="panel">
    <dl class="history-summary">
        <div><dt>Nama</dt><dd>{{ $leaveRequest->student->name }}</dd></div>
        <div><dt>Kelas</dt><dd>{{ $leaveRequest->student->kelas }}</dd></div>
        <div><dt>Jenis izin</dt><dd>{{ $leaveRequest->type }}</dd></div>
        <div><dt>Status saat ini</dt><dd>{{ ucfirst($leaveRequest->status) }}</dd></div>
    </dl>
</section>

<section class="panel">
    <h2>Perubahan status</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Waktu</th><th>Status</th><th>Diubah oleh</th><th>Keterangan</th></tr></thead>
        <tbody>
            @forelse($leaveRequest->histories->sortBy('created_at') as $history)
                <tr>
                    <td>{{ $history->created_at->format('d M Y · H:i') }}</td>
                    <td><span class="badge status-{{ $history->status }}">{{ ucfirst($history->status) }}</span></td>
                    <td>{{ $history->actor?->name ?? 'Sistem' }}</td>
                    <td>{{ $history->note ?: 'Tidak ada keterangan.' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada riwayat perubahan status.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</section>
@endsection
