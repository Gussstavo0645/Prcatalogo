<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f4f6fb, #eef1f7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #111827;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 22px;
            padding: 55px 35px 35px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
            position: relative;
        }

        .logo-circle {
            position: absolute;
            top: -38px;
            left: 50%;
            transform: translateX(-50%);
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899, #c026d3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 22px;
            border: 6px solid #ffffff;
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.35);
        }

        .auth-title {
            text-align: center;
            font-size: 26px;
            font-weight: 800;
            margin: 0;
        }

        .auth-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin: 10px 0 28px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 7px;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
            background: #f9fafb;
        }

        .form-control:focus {
            border-color: #ec4899;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.12);
        }

        .btn-primary {
            width: 100%;
            border: none;
            padding: 13px;
            border-radius: 14px;
            background: linear-gradient(135deg, #ec4899, #c026d3);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(236, 72, 153, 0.30);
        }

        .auth-link {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: #6b7280;
        }

        .auth-link a {
            color: #c026d3;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-link a:hover {
            text-decoration: underline;
        }

        .error-text {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        footer {
            margin-top: 28px;
            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 480px) {
            .auth-card {
                max-width: 90%;
                padding: 55px 24px 30px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-card">

        <div class="logo-circle">
            MRL
        </div>

        <h1 class="auth-title">Crear cuenta</h1>
        <p class="auth-subtitle">
            Regístrate para ingresar al sistema de catálogos.
        </p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input 
                    id="name"
                    type="text"
                    name="name"
                    class="form-control"
                    placeholder="Ejemplo: Gustavo López"
                    value="{{ old('name') }}"
                    required
                    autofocus
                >

                @error('name')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    id="email"
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="ejemplo@correo.com"
                    value="{{ old('email') }}"
                    required
                >

                @error('email')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Mínimo 8 caracteres"
                    required
                >

                @error('password')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input 
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Repite la contraseña"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">
                Crear cuenta
            </button>
        </form>

        <div class="auth-link">
            ¿Ya tienes una cuenta?
            <a href="{{ route('login') }}">Inicia sesión aquí</a>
        </div>

    </div>

    <footer>
        © 2026 Todos los derechos reservados.
    </footer>

</body>
</html>