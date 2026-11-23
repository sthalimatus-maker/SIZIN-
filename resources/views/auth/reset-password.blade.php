<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Password Baru · Izin Siswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <header class="site-header">
        <a class="site-brand" href="{{ route('login') }}">
            <img class="brand-logo" src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMA Negeri 1 Batu Ampar">
            SMA Negeri 1 Batu Ampar
        </a>
        <nav class="site-nav" aria-label="Navigasi utama">
            <a href="{{ route('login') }}">Beranda</a>
            <a href="{{ route('login') }}">Visi Misi</a>
            <a href="{{ route('login') }}">Tentang</a>
            <a href="{{ route('login') }}">Sejarah</a>
            <a class="button button--outline" href="{{ route('login') }}">Masuk</a>
            <a class="button" href="{{ route('register') }}">Daftar Akun</a>
        </nav>
    </header>

    <main class="auth-main">
        <section class="auth-card">
            <p class="eyebrow">Reset password</p>
            <h1>Buat password baru</h1>
            <p class="auth-description">Gunakan password minimal 8 karakter.</p>
            @if ($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required>
                </div>
                <div class="form-group">
                    <label for="password">Password baru</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi password baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
                <button class="button--block" type="submit">Simpan password baru</button>
            </form>
        </section>
    </main>
</body>
</html>
