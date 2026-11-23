@extends('layouts.portal')
@section('title', $leaveRequest ? 'Ubah Pengajuan' : 'Ajukan Izin')
@section('content')
<header class="page-heading"><h1>{{ $leaveRequest ? 'Ubah Pengajuan Izin' : 'Ajukan Izin Keluar Sekolah' }}</h1></header>
<form class="panel" id="leave-request-form" method="post" enctype="multipart/form-data" action="{{ $leaveRequest ? route('student.requests.update',$leaveRequest) : route('student.requests.store') }}" onsubmit="return confirm('Periksa kembali pengajuan izin sebelum dikirim. Lanjutkan?')">@csrf @if($leaveRequest)@method('put')@endif
<div class="form-grid"><div class="form-group"><label for="type">Jenis izin</label><select id="type" name="type" required>@foreach(['Sakit','Keperluan keluarga','Keperluan lain'] as $type)<option @selected(old('type',$leaveRequest?->type ?? 'Sakit')===$type)>{{ $type }}</option>@endforeach</select></div><div class="form-group"><label for="destination">Tujuan</label><input id="destination" name="destination" value="{{ old('destination',$leaveRequest?->destination) }}" placeholder="Contoh: Pulang ke rumah" required></div><div class="form-group span-2"><label for="reason">Alasan</label><textarea id="reason" name="reason" required>{{ old('reason',$leaveRequest?->reason) }}</textarea></div><div class="form-group"><label for="leave_date">Tanggal keluar</label><input id="leave_date" type="date" name="leave_date" value="{{ old('leave_date',$leaveRequest?->leave_date?->format('Y-m-d')) }}" required></div><div class="form-group"><label for="leave_time">Jam keluar</label><input id="leave_time" type="time" name="leave_time" value="{{ old('leave_time',$leaveRequest?->leave_time) }}" required></div><div class="form-group"><label for="return_date">Tanggal kembali</label><input id="return_date" type="date" name="return_date" value="{{ old('return_date',$leaveRequest?->return_date?->format('Y-m-d')) }}" required></div><div class="form-group"><label for="return_time">Jam kembali</label><input id="return_time" type="time" name="return_time" value="{{ old('return_time',$leaveRequest?->return_time) }}" required></div><div class="form-group span-2"><label for="evidence">Bukti (opsional, JPG/PNG/PDF maks. 5 MB)</label><input id="evidence" type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf"></div></div><div class="form-actions"><button type="submit">{{ $leaveRequest ? 'Simpan perubahan' : 'Kirim pengajuan' }}</button><a class="button button--outline" href="{{ route('student.requests.index') }}">Batal</a></div>
</form>
<div class="modal-backdrop" id="submit-confirm" hidden role="presentation">
    <section class="modal" role="alertdialog" aria-modal="true" aria-labelledby="submit-confirm-title" aria-describedby="submit-confirm-message" tabindex="-1">
        <div class="modal-icon modal-icon--info" aria-hidden="true">?</div>
        <h2 id="submit-confirm-title">Periksa pengajuan izin</h2>
        <p id="submit-confirm-message">Pastikan data berikut sudah benar sebelum dikirim untuk diverifikasi.</p>
        <div class="modal-summary">
            <dl>
                <div><dt>Jenis izin</dt><dd data-summary="type">—</dd></div>
                <div><dt>Tujuan</dt><dd data-summary="destination">—</dd></div>
                <div><dt>Waktu</dt><dd data-summary="period">—</dd></div>
                <div><dt>Bukti</dt><dd data-summary="evidence">Tidak ada</dd></div>
            </dl>
        </div>
        <div class="modal-actions">
            <button type="button" class="button button--outline" data-submit-cancel>Periksa kembali</button>
            <button type="button" class="button" data-submit-confirm>{{ $leaveRequest ? 'Ya, simpan perubahan' : 'Ya, kirim pengajuan' }}</button>
        </div>
    </section>
</div>
@endsection
