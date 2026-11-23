<h2>Pembaruan status izin</h2>
<p>Halo {{ $leaveRequest->student->name }},</p>
<p>Pengajuan izin Anda berstatus <strong>{{ ucfirst($leaveRequest->status) }}</strong>.</p>
<dl>
    <dt>Jenis izin</dt><dd>{{ $leaveRequest->type }}</dd>
    <dt>Tanggal keluar</dt><dd>{{ $leaveRequest->leave_date->format('d M Y') }} pukul {{ substr($leaveRequest->leave_time, 0, 5) }}</dd>
    <dt>Tujuan</dt><dd>{{ $leaveRequest->destination }}</dd>
</dl>
@if ($leaveRequest->rejection_reason)
    <p>Catatan guru: {{ $leaveRequest->rejection_reason }}</p>
@endif
<p>Silakan masuk ke Sistem Izin Siswa untuk melihat detail pengajuan.</p>
