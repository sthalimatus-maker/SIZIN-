<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="icon" type="image/png" href="{{ asset('images/logo-sekolah.png') }}"><title>@yield('title', 'Izin Siswa') · SMA Negeri 1 Batu Ampar</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><div class="app-shell">
    <aside class="sidebar"><div class="sidebar-brand"><img class="sidebar-logo" src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMA Negeri 1 Batu Ampar"><span>SMA Negeri 1 Batu Ampar<small>Izin Keluar Masuk Siswa</small></span></div>
        <nav class="sidebar-nav">
            @if(auth()->user()->isTeacher())
                <a href="{{ route('teacher.dashboard') }}" @class(['active' => request()->routeIs('teacher.dashboard')])>Dashboard</a>
                <a href="{{ route('teacher.verification') }}" @class(['active' => request()->routeIs('teacher.verification')])>Verifikasi Izin</a>
                <a href="{{ route('teacher.movements') }}" @class(['active' => request()->routeIs('teacher.movements')])>Keluar Masuk Siswa</a>
                <a href="{{ route('teacher.notifications') }}" @class(['active' => request()->routeIs('teacher.notifications*')])>Notifikasi WhatsApp</a>
                <a href="{{ route('teacher.report') }}" @class(['active' => request()->routeIs('teacher.report')])>Laporan</a>
            @else
                <a href="{{ route('student.dashboard') }}" @class(['active' => request()->routeIs('student.dashboard')])>Dashboard</a>
                <a href="{{ route('student.requests.create') }}" @class(['active' => request()->routeIs('student.requests.create')])>Pengajuan Izin</a>
                <a href="{{ route('student.requests.index') }}" @class(['active' => request()->routeIs('student.requests.*') && !request()->routeIs('student.requests.create')])>Riwayat Status</a>
            @endif
            <form method="post" action="{{ route('logout') }}" data-logout-form>@csrf<button type="submit" class="button--outline" style="width:100%;justify-content:flex-start;background:transparent;border-color:transparent;color:inherit">Logout</button></form>
        </nav><p class="sidebar-user">{{ auth()->user()->name }}{{ auth()->user()->kelas ? ' · '.auth()->user()->kelas : '' }}</p>
    </aside>
    <main class="main-content">
        @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert--error"><ul style="margin:0;padding-left:20px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<div class="modal-backdrop" id="logout-confirm" hidden role="presentation">
    <section class="modal modal--compact" role="alertdialog" aria-modal="true" aria-labelledby="logout-confirm-title" aria-describedby="logout-confirm-message" tabindex="-1">
        <div class="modal-icon modal-icon--warning" aria-hidden="true">!</div>
        <h2 id="logout-confirm-title">Yakin ingin keluar?</h2>
        <p id="logout-confirm-message">Anda akan keluar dari sistem ini.</p>
        <div class="modal-actions">
            <button type="button" class="button button--success" data-logout-confirm>Logout</button>
            <button type="button" class="button button--outline" data-logout-cancel>Batal</button>
        </div>
    </section>
</div>
</body></html>
