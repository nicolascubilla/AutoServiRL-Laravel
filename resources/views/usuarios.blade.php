@extends('layouts.app')

@section('title', 'Usuarios | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-users me-2"></i>Usuarios</h2>
            <p class="text-muted mb-0">Cree y administre los usuarios del sistema</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            @if($usuarios->isEmpty())

                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Todavía no existen usuarios.</p>
                </div>

            @else

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha de Creación</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                                <tr>
                                    <td><strong>{{ $usuario->usuario }}</strong></td>
                                    <td>{{ $usuario->nombre_completo }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td class="text-center">
                                        @if($usuario->estado === 'A')
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ date('d/m/Y H:i', strtotime($usuario->fecha_creacion)) }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Editar nombre"
                                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                                            data-id="{{ $usuario->id_usuario }}"
                                            data-usuario="{{ $usuario->usuario }}"
                                            data-nombre="{{ $usuario->nombre_completo }}"
                                            data-email="{{ $usuario->email }}">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('usuarios.estado') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id_usuario" value="{{ $usuario->id_usuario }}">
                                            @if($usuario->estado === 'A')
                                                @if((int) $usuario->id_usuario === (int) auth()->id())
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled title="No puede inactivar su propia cuenta">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Inactivar"
                                                        onclick="confirmar({ titulo: 'Inactivar usuario', mensaje: '¿Desea inactivar al usuario &quot;{{ $usuario->usuario }}&quot;? No podrá iniciar sesión.', acepText: 'Inactivar', acepClase: 'btn-danger', icono: 'fa-user-slash', iconoClase: 'text-danger' }).then(ok => { if (ok) { this.closest('form').submit(); } });">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Activar"
                                                    onclick="confirmar({ titulo: 'Activar usuario', mensaje: '¿Desea activar al usuario &quot;{{ $usuario->usuario }}&quot;?', acepText: 'Activar', acepClase: 'btn-success', icono: 'fa-user-check', iconoClase: 'text-success' }).then(ok => { if (ok) { this.closest('form').submit(); } });">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

        </div>
    </div>
</div>

<!-- ===========================
MODAL NUEVO USUARIO
=========================== -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="frmUsuario" method="POST" action="{{ route('usuarios.guardar') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel"><i class="fas fa-user-plus me-1"></i> Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="usuario">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="usuario" name="usuario" required
                            maxlength="50" autocomplete="off" placeholder="Nombre de usuario para acceder">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="nombre_completo">Nombre completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required
                            maxlength="100" placeholder="Nombre y apellido">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required
                            maxlength="100" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="form-text">Obligatorio: se usa para enviar el código de recuperación de contraseña.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="contrasena">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="contrasena" name="contrasena"
                            required autocomplete="new-password" minlength="6" placeholder="Mínimo 6 caracteres">
                        <button type="button" class="btn btn-outline-secondary toggle-pass-usuario" data-target="contrasena" tabindex="-1" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="contrasena_repite">Confirmar contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-check"></i></span>
                        <input type="password" class="form-control" id="contrasena_repite"
                            required autocomplete="new-password" minlength="6" placeholder="Repetí la contraseña">
                        <button type="button" class="btn btn-outline-secondary toggle-pass-usuario" data-target="contrasena_repite" tabindex="-1" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="invalid-feedback" id="msgNoCoincide">Las contraseñas no coinciden.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================
MODAL EDITAR USUARIO
=========================== -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="frmEditar" method="POST" action="{{ route('usuarios.editar') }}" class="modal-content">
            @csrf
            <input type="hidden" id="editar_id" name="id_usuario">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit me-1"></i> Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="editar_usuario" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" id="editar_email" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="editar_nombre">Nombre completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="editar_nombre" name="nombre_completo" required
                            maxlength="100" placeholder="Nombre y apellido">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalUsuario');
    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('frmUsuario').reset();
    });
    document.getElementById('frmUsuario').addEventListener('submit', function (e) {
        var pass = document.getElementById('contrasena').value;
        var repite = document.getElementById('contrasena_repite').value;
        var campo = document.getElementById('contrasena_repite');
        if (pass !== repite) {
            e.preventDefault();
            campo.classList.add('is-invalid');
            return;
        }
        campo.classList.remove('is-invalid');
    });
    document.getElementById('contrasena_repite').addEventListener('input', function () {
        this.classList.remove('is-invalid');
    });

    document.querySelectorAll('.toggle-pass-usuario').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var campo = document.getElementById(this.getAttribute('data-target'));
            var visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            this.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            this.innerHTML = visible ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });

    var modalEditar = document.getElementById('modalEditar');
    modalEditar.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        document.getElementById('editar_id').value = btn.getAttribute('data-id');
        document.getElementById('editar_usuario').value = btn.getAttribute('data-usuario');
        document.getElementById('editar_email').value = btn.getAttribute('data-email');
        document.getElementById('editar_nombre').value = btn.getAttribute('data-nombre');
    });
});
</script>
@endsection