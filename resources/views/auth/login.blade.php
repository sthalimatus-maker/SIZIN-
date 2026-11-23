<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk · Izin Siswa</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="auth-page"><header class="site-header"><a class="site-brand" href="{{ route('login') }}"><img class="brand-logo" src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMA Negeri 1 Batu Ampar">SMA Negeri 1 Batu Ampar</a><nav class="site-nav"><a href="{{ route('login') }}">Beranda</a><a href="{{ route('login') }}">Visi Misi</a><a href="{{ route('login') }}">Tentang</a><a href="{{ route('login') }}">Sejarah</a><a class="button button--outline" href="{{ route('login') }}">Masuk</a><a class="button" href="{{ route('register') }}">Daftar Akun</a></nav></header>
<main class="auth-main"><section class="auth-card"><h1>Masuk</h1>
    @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert--error">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('login.store') }}">@csrf<div class="form-group"><label for="username">Username atau email</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus></div><div class="form-group"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div><button type="submit" class="button--block">Masuk</button></form><div class="auth-links"><a href="{{ route('password.request') }}">Lupa password?</a><a href="{{ route('register') }}">Daftar akun</a></div><hr class="auth-divider"><p class="auth-note">Akun guru dibuat oleh administrator sekolah.</p></section></main>
<footer class="site-footer"><p>© {{ now()->year }} SMA Negeri 1 Batu Ampar</p><p>Sistem Informasi Izin Keluar Masuk Siswa</p></footer></body></html>
