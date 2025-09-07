<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Absensi PT. ASA</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('images/logo_perusahaan.png') }}" alt="Logo Perusahaan" class="logo-icon">
                <h2>Selamat Datang</h2>
                <p>Silahkan login, untuk melakukan absen</p>
            </div>

            {{-- pesan error global --}}
            @if ($errors->any())
                <div class="error-message-global">
                    {{ $errors->first('login') }}
                </div>
            @endif

            <form class="login-form" id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="number" id="nip" name="nip" required autocomplete="username">
                        <label for="nip">NIP</label>
                        <span class="input-border"></span>
                    </div>
                    <span class="error-message">@error('nip') {{ $message }} @enderror</span>
                </div>

                <div class="form-group">
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="toggle-icon"></span>
                        </button>
                        <span class="input-border"></span>
                    </div>
                    <span class="error-message">@error('password') {{ $message }} @enderror</span>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Login</span>
                    <span class="btn-loader"></span>
                </button>
            </form>
        </div>
    </div>
</body>

</html>
