<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Marlen Lamur') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Figtree', Arial, Helvetica, sans-serif;
            color: #ffffff;
            background:
                linear-gradient(rgba(34, 14, 45, .68), rgba(21, 18, 47, .84)),
                url('{{ asset('imagenes/login-cosmeticos.jpg') }}') center center / cover no-repeat,
                linear-gradient(135deg, #8f1f68, #3122a8);
        }

        .login-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .login-shell::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            top: -220px;
            right: -150px;
            background: rgba(255, 255, 255, .10);
            filter: blur(2px);
        }

        .login-shell::after {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            bottom: -180px;
            left: -120px;
            background: rgba(244, 114, 182, .18);
            filter: blur(2px);
        }

        .login-header {
            position: relative;
            z-index: 5;
            width: 100%;
            padding: 22px 42px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .login-brand {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .4px;
            padding: 11px 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: .25s ease;
        }

        .login-brand:hover {
            color: white;
            transform: translateY(-1px);
            background: rgba(255, 255, 255, .20);
        }

        .login-main {
            flex: 1;
            position: relative;
            z-index: 2;
            display: grid;
            place-items: center;
            padding: 25px 18px 45px;
        }

        .login-card {
            width: 100%;
            max-width: 550px;
            padding: 34px 42px 36px;
            border-radius: 30px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .24);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, .38),
                inset 0 1px 0 rgba(255, 255, 255, .18);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .login-logo-wrap {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo-frame {
            position: relative;
            width: 100%;
            height: 190px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }

        .login-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 470px;
            max-width: none;
            height: auto;
            transform: translate(-50%, -50%) scale(1.22);
            display: block;
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, .34));
        }

        .login-subtitle {
            margin: 0;
            color: rgba(255, 255, 255, .92);
            font-size: 14px;
            line-height: 1.45;
            font-weight: 500;
        }

        .login-slot h2,
        .login-slot h1 {
            color: white !important;
            font-size: 28px !important;
            font-weight: 800 !important;
            text-align: center !important;
            margin-bottom: 22px !important;
        }

        .login-slot label {
            color: rgba(255, 255, 255, .94) !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        .login-slot input[type="email"],
        .login-slot input[type="password"],
        .login-slot input[type="text"] {
            width: 100% !important;
            min-height: 52px !important;
            border-radius: 999px !important;
            padding: 0 20px !important;
            color: white !important;
            background: rgba(255, 255, 255, .16) !important;
            border: 1px solid rgba(255, 255, 255, .26) !important;
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, .05) !important;
        }

        .login-slot input::placeholder {
            color: rgba(255, 255, 255, .70) !important;
        }

        .login-slot input:focus {
            outline: none !important;
            border-color: rgba(255, 255, 255, .68) !important;
            background: rgba(255, 255, 255, .22) !important;
            box-shadow:
                0 0 0 4px rgba(236, 72, 153, .18),
                inset 0 1px 3px rgba(255, 255, 255, .05) !important;
        }

        .login-slot button,
        .login-slot .inline-flex.items-center.px-4.py-2 {
            width: 100% !important;
            min-height: 52px !important;
            justify-content: center !important;
            border: none !important;
            border-radius: 999px !important;
            background: linear-gradient(90deg, #c2185b, #7c2ed6) !important;
            color: white !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            letter-spacing: .5px !important;
            text-transform: uppercase !important;
            box-shadow: 0 16px 30px rgba(194, 24, 91, .30) !important;
            transition: .25s ease !important;
        }

        .login-slot button:hover,
        .login-slot .inline-flex.items-center.px-4.py-2:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
        }

        .login-slot a {
            color: rgba(255, 255, 255, .96) !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }

        .login-slot a:hover {
            text-decoration: underline !important;
        }

        .login-slot .text-sm.text-gray-600,
        .login-slot .text-sm.text-gray-700,
        .login-slot .text-gray-600,
        .login-slot .text-gray-900 {
            color: rgba(255, 255, 255, .90) !important;
        }

        .login-slot .block.mt-4,
        .login-slot .mt-4 {
            margin-top: 18px !important;
        }

        .login-slot .flex.items-center.justify-end.mt-4 {
            flex-direction: column-reverse !important;
            align-items: stretch !important;
            gap: 16px !important;
        }

        .login-slot input[type="checkbox"] {
            accent-color: #ec4899;
        }

        .login-footer {
            position: relative;
            z-index: 3;
            text-align: center;
            padding: 0 18px 26px;
            color: rgba(255, 255, 255, .82);
            font-size: 13px;
        }

        @media (max-width: 640px) {
            .login-header {
                padding: 18px;
            }

            .login-main {
                padding: 18px 14px 32px;
            }

            .login-card {
                max-width: 100%;
                padding: 28px 22px 30px;
                border-radius: 26px;
            }

            .login-logo-frame {
                height: 130px;
            }

            .login-logo {
                width: 395px;
                transform: translate(-50%, -50%) scale(1.16);
            }

            .login-slot h2,
            .login-slot h1 {
                font-size: 25px !important;
            }
        }
    </style>
</head>

<body>
    <div class="login-shell">

        <header class="login-header">
            <a href="{{ url('/') }}" class="login-brand">
                <span>←</span>
                <span>Volver al inicio</span>
            </a>
        </header>

        <main class="login-main">
            <section class="login-card">

                <div class="login-logo-wrap">
                    <div class="login-logo-frame">
                        <img
                            src="{{ asset('imagenes/LOGO1.png') }}"
                            alt="Marlen Lamur"
                            class="login-logo"
                        >
                    </div>

                    <p class="login-subtitle">
                        Accede al panel administrativo del catálogo digital.
                    </p>
                </div>

                <div class="login-slot">
                    {{ $slot }}
                </div>

            </section>
        </main>

        <footer class="login-footer">
            © {{ date('Y') }} Marlen Lamur · Todos los derechos reservados.
        </footer>

    </div>
</body>
</html>