<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | Autoservice R & L</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Arial, sans-serif; }
        .login-container { display: flex; min-height: 100vh; }

        .login-image {
            flex: 1; position: relative;
            background: url('{{ asset('assets/images/Autoservi.jpeg') }}');
            background-size: cover; background-position: center;
            background-repeat: no-repeat; overflow: hidden;
        }
        .login-image::before {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(8,20,30,0.75) 0%, rgba(8,20,30,0.35) 50%, rgba(8,20,30,0.75) 100%);
            z-index: 1;
        }
        .image-content {
            position: relative; z-index: 2; height: 100%;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem 3.5rem; color: #fff;
        }
        .brand-top {
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 1.4rem; font-weight: 700; letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.4);
        }
        .brand-top i { font-size: 1.8rem; color: #8de3a0; }
        .image-text { max-width: 480px; }
        .image-text h1 {
            font-size: 3rem; font-weight: 800; line-height: 1.1;
            margin-bottom: 1rem; text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        .image-text .badge-line {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);
            padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.95rem;
            margin-bottom: 1.2rem; border: 1px solid rgba(255,255,255,0.25);
        }
        .image-text p { font-size: 1.1rem; opacity: 0.92; line-height: 1.6; }
        .brand-footer { font-size: 0.85rem; opacity: 0.8; }

        .login-form {
            flex: 1; width: 100%; max-width: 560px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            display: flex; justify-content: center; align-items: center; padding: 3rem 2.5rem;
        }
        .form-wrap { width: 100%; max-width: 400px; }
        .form-header { text-align: center; margin-bottom: 2rem; }
        .form-logo {
            width: 68px; height: 68px; margin: 0 auto 1rem; border-radius: 18px;
            background: linear-gradient(135deg, #17a2b8, #20c997);
            display: flex; align-items: center; justify-content: center; color: #fff;
            font-size: 1.7rem; box-shadow: 0 10px 22px rgba(23,162,184,0.35);
        }
        .form-header h2 { font-weight: 700; font-size: 1.75rem; color: #1e293b; margin-bottom: 0.35rem; }
        .form-header p { color: #64748b; font-size: 0.95rem; }
        .field { margin-bottom: 1.25rem; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 0.95rem;
        }
        .input-wrapper input {
            width: 100%; padding: 0.8rem 1rem 0.8rem 42px;
            border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;
            background: #fff; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper input:focus { outline: none; border-color: #17a2b8; box-shadow: 0 0 0 4px rgba(23,162,184,0.12); }
        .btn-login {
            width: 100%; padding: 0.85rem; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #17a2b8, #0f8fa8); color: #fff;
            font-size: 1rem; font-weight: 600; display: flex; align-items: center;
            justify-content: center; gap: 0.5rem; cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 8px 18px rgba(23,162,184,0.3); margin-top: 0.5rem;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(23,162,184,0.38); }
        .btn-login:active { transform: translateY(0); opacity: 0.92; }
        .alert-error {
            display: flex; align-items: center; gap: 0.6rem;
            background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;
            border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.9rem; margin-bottom: 1.25rem;
        }
        .alert-error i { color: #ef4444; }
        .form-foot { text-align: center; margin-top: 1.5rem; color: #94a3b8; font-size: 0.8rem; }
        .form-foot i { color: #ef4444; }

        @media (max-width: 992px) {
            .login-image { display: none; }
            .login-form { flex: 1; max-width: none; width: 100%; padding: 2.5rem 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="login-container">

        <div class="login-image">
            <div class="image-content">
                <div class="brand-top">
                    <i class="fas fa-shopping-cart"></i>
                    Autoservice R & L
                </div>
                <div class="image-text">
                    <div class="badge-line">
                        <i class="fas fa-asterisk"></i>
                        Sistema de Gestión y Ventas
                    </div>
                    <h1>Tu negocio,<br>al mejor control.</h1>
                    <p>
                        Gestiona tus ventas, productos y caja de forma rápida y sencilla
                        desde un solo lugar.
                    </p>
                </div>
                <div class="brand-footer">
                    &copy; 2026 Autoservice R & L &mdash; Todos los derechos reservados.
                </div>
            </div>
        </div>

        <div class="login-form">
            <div class="form-wrap">
                <div class="form-header">
                    <div class="form-logo"><i class="fas fa-user-lock"></i></div>
                    <h2>Bienvenido</h2>
                    <p>Inicie sesión para continuar</p>
                </div>

                @if (isset($errors) && $errors->has('login'))
                    <div class="alert-error" id="alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $errors->first('login') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert-error" id="alert-success"
                        style="background:#f0fdf4;color:#15803d;border-color:#bbf7d0;">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                    @csrf
                    <div class="field">
                        <label for="usuario">Usuario</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}" placeholder="Ingrese su usuario" required autofocus>
                        </div>
                    </div>
                    <div class="field">
                        <label for="contrasena">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('password.forgot') }}" style="color:#17a2b8;font-size:0.9rem;text-decoration:none;font-weight:600;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <div class="form-foot">
                    Autoservice R & L &copy; 2026
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const errorMessage = document.getElementById('alert-error');
            if (errorMessage) {
                setTimeout(function () {
                    errorMessage.style.transition = "opacity 0.5s ease";
                    errorMessage.style.opacity = "0";
                    setTimeout(function () { errorMessage.remove(); }, 500);
                }, 4000);
            }
        });
    </script>
</body>
</html>
