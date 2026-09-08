<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de verificación | Autoservice R &amp; L</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#17a2b8,#20c997);padding:22px 28px;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;">
                                <span style="font-size:22px;">🛒</span>&nbsp;Autoservice R & L
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            @if($nombre !== '')
                                <p style="font-size:16px;color:#1e293b;">Hola, <strong>{{ $nombre }}</strong>:</p>
                            @else
                                <p style="font-size:16px;color:#1e293b;">Hola:</p>
                            @endif
                            <p style="font-size:15px;color:#475569;line-height:1.6;">
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta.
                                Usá el siguiente código de verificación para continuar:
                            </p>
                            <div style="text-align:center;margin:24px 0;">
                                <span style="display:inline-block;background:#e7f1ff;border:2px dashed #0d6efd;border-radius:10px;padding:14px 28px;font-size:30px;font-weight:800;letter-spacing:6px;color:#0d6efd;">
                                    {{ $codigo }}
                                </span>
                            </div>
                            <p style="font-size:13px;color:#64748b;line-height:1.5;">
                                El código expira en <strong>10 minutos</strong>. Si vos no solicitaste este cambio,
                                podés ignorar este correo y tu contraseña seguirá igual.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="font-size:12px;color:#94a3b8;margin:0;">
                                AutoServiRL &mdash; Sistema de Gestión y Ventas &middot; &copy; 2026
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>