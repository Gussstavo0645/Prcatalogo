<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            background:
                radial-gradient(circle at top right, rgba(220, 53, 184, .14), transparent 32%),
                radial-gradient(circle at bottom left, rgba(220, 53, 184, .10), transparent 30%),
                #f7f8fb;
        }

        .login-header {
            width: 100%;
            background: linear-gradient(90deg, #d93fc4, #dc35b8);
            color: white;
            box-shadow: 0 4px 20px rgba(220, 53, 184, .25);
        }

        .login-header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .login-brand {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: .5px;
        }

        .login-brand-icon {
            width: 28px;
            height: 28px;
            border: 2px solid white;
            border-radius: 8px;
            transform: rotate(45deg);
            display: inline-block;
        }

        .login-links {
            display: flex;
            gap: 28px;
        }

        .login-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }

        .login-page {
            min-height: calc(100vh - 72px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 55px 18px 30px;
            position: relative;
            overflow: hidden;
        }

        .login-dots {
            position: absolute;
            width: 150px;
            height: 150px;
            background-image: radial-gradient(rgba(31, 41, 55, .14) 2px, transparent 2px);
            background-size: 22px 22px;
            opacity: .45;
        }

        .login-dots.left {
            left: 55px;
            top: 90px;
        }

        .login-dots.right {
            right: 75px;
            bottom: 85px;
        }

        .login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, .96);
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 25px 55px rgba(15, 23, 42, .16);
            padding: 72px 38px 32px;
        }

        .login-logo-circle {
            position: absolute;
            top: -48px;
            left: 50%;
            transform: translateX(-50%);
            width: 96px;
            height: 96px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
        }

        .login-logo-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ec4899, #d946ef);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 22px;
        }

        .login-footer {
            position: relative;
            z-index: 2;
            margin-top: 32px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 640px) {
            .login-header-inner {
                flex-direction: column;
                gap: 10px;
                padding: 16px;
            }

            .login-brand {
                font-size: 17px;
            }

            .login-links {
                gap: 18px;
            }

            .login-card {
                padding: 70px 22px 28px;
            }

            .login-dots {
                display: none;
            }
        }
    </style>
</head>

<body>

    <header class="login-header">
        <div class="login-header-inner">
            <a href="{{ url('/') }}" class="login-brand">
                <span class="login-brand-icon"></span>
                <span>INICIO</span>
            </a>

            <nav class="login-links">
                <a href="#"></a>
                <a href="#"></a>
            </nav>
        </div>
    </header>

    <main class="login-page">
        <div class="login-dots left"></div>
        <div class="login-dots right"></div>

        <section class="login-card">
            <div class="login-logo-circle">
                <div class="login-logo-box">MRL</div>
            </div>

            {{ $slot }}
        </section>

        <p class="login-footer">
            © {{ date('Y') }} Todos los derechos reservados.
        </p>
    </main>

</body>
</html>