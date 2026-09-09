@extends('layouts.app')

@section('title', 'Cambiar Contraseña | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h2>
            <p class="text-muted mb-0">Actualizá la contraseña de tu cuenta de acceso al sistema.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('cambiar_contrasena_guardar') }}" id="formContrasena">
                        @csrf
<div class="mb-3">
                            <label class="form-label" for="actual">Contraseña actual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="actual" name="actual"
                                    required autocomplete="current-password" placeholder="Ingresá tu contraseña actual">
                                <button type="button" class="btn btn-outline-secondary toggle-pass-btn" data-target="actual" tabindex="-1" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nueva">Nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-unlock"></i></span>
                                <input type="password" class="form-control" id="nueva" name="nueva"
                                    required autocomplete="new-password" minlength="6" placeholder="Mínimo 6 caracteres">
                                <button type="button" class="btn btn-outline-secondary toggle-pass-btn" data-target="nueva" tabindex="-1" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="repite">Confirmar nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-check"></i></span>
                                <input type="password" class="form-control" id="repite" name="repite"
                                    required autocomplete="new-password" minlength="6" placeholder="Repetí la nueva contraseña">
                                <button type="button" class="btn btn-outline-secondary toggle-pass-btn" data-target="repite" tabindex="-1" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formContrasena');
    form.addEventListener('submit', function (e) {
        var nueva = document.getElementById('nueva').value;
        var repite = document.getElementById('repite').value;
        if (nueva !== repite) {
            e.preventDefault();
            document.getElementById('repite').classList.add('is-invalid');
            var msg = document.getElementById('msgCoincidencia');
            if (!msg) {
                msg = document.createElement('div');
                msg.id = 'msgCoincidencia';
                msg.className = 'invalid-feedback';
                msg.textContent = 'Las contraseñas no coinciden.';
                document.getElementById('repite').parentNode.appendChild(msg);
            }
        }
var actual = document.getElementById('actual');
        if (!actual.value) {
            actual.classList.add('is-invalid');
            e.preventDefault();
        }
    });

    document.querySelectorAll('.toggle-pass-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var campo = document.getElementById(this.getAttribute('data-target'));
            var visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            this.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            this.innerHTML = visible ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
});
</script>
@endsection
