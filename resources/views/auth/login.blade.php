<x-guest-layout>

    <style>
        .login-title {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-title h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            color: #1f2937;
        }

        .login-title p {
            margin-top: 10px;
            font-size: 16px;
            color: #6b7280;
        }

        .login-line {
            height: 1px;
            background: #e5e7eb;
            margin-bottom: 26px;
        }

        .form-space {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-block label {
            display: block;
            font-weight: 700;
            font-size: 15px;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-block input[type="email"],
        .form-block input[type="password"] {
            width: 100%;
            height: 54px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 15px;
            outline: none;
            transition: .2s;
        }

        .form-block input:focus {
            border-color: #ec4899;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, .13);
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .remember-label input {
            width: 17px;
            height: 17px;
            accent-color: #ec4899;
        }

        .login-link {
            color: #ec4899;
            font-weight: 700;
            text-decoration: none;
        }

        .login-link:hover {
            color: #db2777;
        }

        .login-button {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #ec4899, #d946ef);
            color: white;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(236, 72, 153, .28);
            transition: .2s;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(236, 72, 153, .36);
        }

        .register-area {
            border-top: 1px solid #e5e7eb;
            margin-top: 26px;
            padding-top: 22px;
            text-align: center;
            color: #6b7280;
            font-size: 15px;
        }

        .error-text {
            margin-top: 7px;
            font-size: 13px;
            color: #dc2626;
        }

        @media (max-width: 480px) {
            .login-title h1 {
                font-size: 28px;
            }

            .login-options {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="login-title">
        <h1>Iniciar sesión</h1>
        <p>Accede a tu panel de administración</p>
    </div>

    <div class="login-line"></div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="form-space">
        @csrf

        <div class="form-block">
            <label for="email">Correo electrónico</label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="ejemplo@correo.com"
            >

            @error('email')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-block">
            <label for="password">Contraseña</label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Tu contraseña"
            >

            @error('password')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="login-options">
            <label for="remember_me" class="remember-label">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Recordarme</span>
            </label>

         {{--    @if (Route::has('password.request'))
                <a class="login-link" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif--}} 
        </div>

        <button type="submit" class="login-button">
            Entrar →
        </button>

    {{--   @if (Route::has('register'))
            <div class="register-area">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="login-link">Registrarme</a>
            </div>
        @endif--}}
    </form>

</x-guest-layout>