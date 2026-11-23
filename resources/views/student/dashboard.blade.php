@extends('layouts.portal')
@section('title','Dashboard Siswa')
@section('content')
<header class="page-heading"><h1>Dashboard</h1><a class="button" href="{{ route('student.requests.create') }}">+ Ajukan Izin</a></header>
<section class="welcome-card"><h2>Halo, {{ auth()->user()->name }}</h2><p>{{ auth()->user()->kelas }} · Ajukan izin keluar sekolah dan pantau statusnya di sini.</p></section>
<section class="stats-grid">@foreach(['menunggu'=>'Menunggu verifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai'] as $key=>$label)<article class="stat-card"><span>{{ $label }}</span><strong>{{ $counts[$key] ?? 0 }}</strong></article>@endforeach</section>
<section class="panel"><h2>Pengajuan terbaru</h2><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jenis izin</th><th>Alasan</th><th>Status</th><th></th></tr></thead><tbody>@forelse($requests as $item)<tr><td>{{ $item->leave_date->format('d M Y') }}</td><td>{{ $item->type }}</td><td>{{ $item->reason }}</td><td><span class="badge status-{{ $item->status }}">{{ ucfirst($item->status) }}</span></td><td><a href="{{ route('student.requests.show',$item) }}">Detail</a></td></tr>@empty<tr><td colspan="5">Belum ada pengajuan izin.</td></tr>@endforelse</tbody></table></div><p style="margin:18px 0 0"><a href="{{ route('student.requests.index') }}">Lihat semua →</a></p></section>
@endsection
