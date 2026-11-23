<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password · Izin Siswa</title>
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
            <p class="eyebrow">Lupa password</p>
            <h1>Reset password</h1>
            <p class="auth-description">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.</p>
            @if (session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <button class="button--block" type="submit">Kirim tautan reset</button>
            </form>
            <p class="auth-note" style="margin-top:18px;text-align:center"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
        </section>
    </main>
</body>
</html>
