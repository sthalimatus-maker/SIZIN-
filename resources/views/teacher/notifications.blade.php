@extends('layouts.portal')
@section('title', 'Riwayat Notifikasi')
@section('content')
<header class="page-heading"><h1>Riwayat Notifikasi</h1></header>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Waktu</th><th>Media</th><th>Tujuan</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($notifications as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d M Y · H:i') }}</td>
                        <td>{{ strtoupper($item->channel) }}</td>
                        <td>{{ $item->recipient }}</td>
                        <td>{{ $item->message }}</td>
                        <td>
                            <span class="badge {{ $item->status === 'terkirim' ? 'status-terkirim' : ($item->status === 'gagal' ? 'status-gagal' : 'status-menunggu') }}">{{ ucfirst($item->status) }}</span>
                            <small style="display:block;color:var(--muted)">{{ $item->response }}</small>
                        </td>
                        <td>
                            @if ($item->status !== 'terkirim')
                                <form method="post" action="{{ route('teacher.notifications.retry', $item) }}">@csrf<button class="button--small">Kirim ulang</button></form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada notifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $notifications->links() }}</div>
    <p class="auth-note" style="margin-top:18px">Email hanya digunakan untuk reset password. Notifikasi status izin dikirim melalui WhatsApp; kanal ini memerlukan token, phone number ID, dan template yang sudah disetujui Meta.</p>
</section>
@endsection
