<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - ConnectPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            padding: 40px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .logo-badge {
            background: #2563eb;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-top: 8px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.2s ease;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 32px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-section">
        <div class="logo-text">
            <span>ConnectPay</span>
            <span class="logo-badge">Admin</span>
        </div>
        <p class="subtitle">Silakan masuk untuk mengelola tagihan</p>
    </div>

    @if ($errors->has('login_error'))
        <div class="alert-error">
            ⚠️ {{ $errors->first('login_error') }}
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="{{ old('username') }}" 
                placeholder="Masukkan username" 
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="••••••••" 
                required
            >
        </div>

        <button type="submit" class="btn-submit">
            Masuk Sekarang
        </button>
    </form>

    <div class="footer-text">
        &copy; 2026 ConnectPay Portal. All rights reserved.
    </div>
</div>

</body>
</html>
