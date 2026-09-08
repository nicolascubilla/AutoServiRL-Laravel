<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Código | Autoservice R &amp; L</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Arial, sans-serif; }
        body {
            background: linear-gradient(135deg, rgba(8,20,30,0.9), rgba(8,20,30,0.7)),
                        url('{{ asset('assets/images/Autoservi.jpeg') }}');
            background-size: cover; background-position: center;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1.5rem;
        }
        .card-auth { width: 100%; max-width: 420px; }
        .card-body { padding: 2.2rem 2rem; }
        .form-logo {
            width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 18px;
            background: linear-gradient(135deg, #17a2b8, #20c997);
            display: flex; align-items: center; justify-content: center; color: #fff;
            font-size: 1.6rem; box-shadow: 0 10px 22px rgba(23,162,184,0.35);
        }
        .form-header { text-align: center; margin-bottom: 1.6rem; }
        .form-header h2 { font-weight: 700; font-size: 1.5rem; color: #1e293b; margin-bottom: 0.3rem; }
        .form-header p { color: #64748b; font-size: 0.9rem; }
        .field { margin-bottom: 1.1rem; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 0.95rem;
        }
        .input-wrapper input {
            width: 100%; padding: 0.8rem 1rem 0.8rem 42px;
            border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper input:focus { outline: none; border-color: #17a2b8; box-shadow: 0 0 0 4px rgba(23,162,184,0.12); }
        .btn-auth {
            width: 100%; padding: 0.85rem; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #17a2b8, #0f8fa8); color: #fff;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 8px 18px rgba(23,162,184,0.3); margin-top: 0.3rem;
        }
        .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(23,162,184,0.38); }
        .alert-box {
            border-radius: 12px; padding: 0.7rem 1rem; font-size: 0.9rem; margin-bottom: 1.1rem;
        }
        .alert-box.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-box.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .form-foot { text-align: center; margin-top: 1.4rem; }
        .form-foot a { color: #17a2b8; font-size: 0.9rem; text-decoration: none; font-weight: 600; }
        .form-foot .reintentar { color: #b45309; font-size: 0.85rem; display: block; margin-top: 0.5rem; }
    </style>
</head>

<body>
    <div class="card card-auth border-0 shadow-lg">
        <div class="card-body">
            <div class="form-header">
                <div class="form-logo"><i class="fas fa-shield-alt"></i></div>
                <h2>Ingresá el código</h2>
                <p>Ingresá el código de 6 dígitos enviado a tu correo y definí tu nueva contraseña.</p>
            </div>

            @if (session('error'))
                <div class="alert-box error"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert-box success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.restablecer') }}">
                @csrf
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}"
                            placeholder="nombre@correo.com" required>
                    </div>
                </div>
                <div class="field">
                    <label for="codigo">Código de verificación</label>
                    <div class="input-wrapper">
                        <i class="fas fa-hashtag"></i>
                        <input type="text" id="codigo" name="codigo" value="{{ old('codigo') }}" maxlength="6"
                            inputmode="numeric" pattern="[0-9]{6}" placeholder="123456" required>
                    </div>
                </div>
                <div class="field">
                    <label for="nueva">Nueva contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="nueva" name="nueva" minlength="6"
                            placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>
                <div class="field">
                    <label for="repite">Repetir contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="repite" name="repite" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn-auth">
                    <i class="fas fa-check-circle me-1"></i> Restablecer Contraseña
                </button>
            </form>

            <div class="form-foot">
                <a href="{{ route('password.forgot') }}" class="reintentar">
                    <i class="fas fa-redo-alt me-1"></i>¿No te llegó el código? Reenviar
                </a>
                <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>

</html>