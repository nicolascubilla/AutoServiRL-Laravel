<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'AutoServiRL')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('assets/css/main-QHWFdn9T.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5"></script>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">

<header class="topbar">
    <div class="d-flex align-items-center topbar-left">
        <button id="toggleMenu" class="menu-toggle" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="topbar-brand">
            <span class="topbar-logo">🛒</span>
            <span class="topbar-title">AutoServiRL</span>
        </a>
    </div>

    <div class="d-flex align-items-center topbar-actions">
        <span class="topbar-user">
            <i class="fas fa-user-circle user-icon"></i>
            <span class="user-name">{{ session('nombre') }}</span>
        </span>

        <a href="{{ route('cambiar_contrasena') }}" class="btn btn-outline-secondary btn-sm me-1">
            <i class="fas fa-key d-md-none"></i>
            <span class="d-none d-md-inline">Cambiar contraseña</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt d-md-none"></i>
                <span class="d-none d-md-inline">Salir</span>
            </button>
        </form>
    </div>
</header>

<div class="app-layout">
    <aside id="left-panel" class="left-panel">
        <div class="sidebar-menu">
            <div class="sidebar-brand">
                <span class="sb-brand-icon"><i class="fas fa-store"></i></span>
                <span class="nav-text sb-brand-text">AutoServiRL</span>
            </div>

            <div class="sb-section">
                <span class="sb-section-title">Menú principal</span>
            </div>

            <nav class="nav-menu">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="nav-icon fas fa-home"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="{{ route('ventas') }}" class="nav-link">
                    <i class="nav-icon fas fa-cash-register"></i>
                    <span class="nav-text">Ventas</span>
                </a>
                <a href="{{ route('ventas.historial') }}" class="nav-link">
                    <i class="nav-icon fas fa-clock-rotate-left"></i>
                    <span class="nav-text">Historial de Ventas</span>
                </a>
                <a href="{{ route('productos') }}" class="nav-link">
                    <i class="nav-icon fas fa-box"></i>
                    <span class="nav-text">Productos</span>
                </a>
                <a href="{{ route('stock') }}" class="nav-link">
                    <i class="nav-icon fas fa-warehouse"></i>
                    <span class="nav-text">Stock</span>
                </a>
                <a href="{{ route('caja') }}" class="nav-link">
                    <i class="nav-icon fas fa-money-bill-wave"></i>
                    <span class="nav-text">Caja</span>
                </a>
                <a href="{{ route('caja.historial') }}" class="nav-link">
                    <i class="nav-icon fas fa-clock-rotate-left"></i>
                    <span class="nav-text">Historial de Cajas</span>
                </a>

                <div class="sb-section">
                    <span class="sb-section-title">Facturación</span>
                </div>
                <a href="{{ route('factura.historial') }}" class="nav-link">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <span class="nav-text">Historial de Facturas</span>
                </a>
                <a href="{{ route('facturacion.config') }}" class="nav-link">
                    <i class="nav-icon fas fa-gear"></i>
                    <span class="nav-text">Configuración</span>
                </a>

                <div class="sb-section">
                    <span class="sb-section-title">Reportes</span>
                </div>
                <a href="{{ route('informes') }}" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span class="nav-text">Informes</span>
                </a>

                <div class="sb-section">
                    <span class="sb-section-title">Sistema</span>
                </div>
                <a href="{{ route('usuarios') }}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
                <a href="{{ route('cambiar_contrasena') }}" class="nav-link">
                    <i class="nav-icon fas fa-key"></i>
                    <span class="nav-text">Cambiar Contraseña</span>
                </a>
                <a href="/manual/MANUAL_USUARIO_AUTOSERVICE.pdf" target="_blank" class="nav-link">
                    <i class="nav-icon fas fa-book"></i>
                    <span class="nav-text">Manual de Usuario</span>
                </a>
            </nav>
        </div>
    </aside>

    <div id="overlay"></div>

    <div class="content-wrapper">
        <div class="app-main-content">
            @yield('content')
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small>© 2026 AutoServiRL - Todos los derechos reservados.</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>Version 1.0</small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const btn = document.getElementById("toggleMenu");
const sidebar = document.getElementById("left-panel");
const overlay = document.getElementById("overlay");
const body = document.body;

function isMobileLayout() { return window.innerWidth <= 1024; }

function closeSidebar() {
    if (sidebar) { sidebar.classList.remove("show"); sidebar.classList.remove("collapsed"); }
    if (overlay) { overlay.classList.remove("show"); }
    body.classList.remove("sidebar-open-mobile");
    body.classList.remove("sidebar-collapsed");
}

function openMobileSidebar() {
    if (sidebar) { sidebar.classList.add("show"); }
    if (overlay) { overlay.classList.add("show"); }
    body.classList.add("sidebar-open-mobile");
}

if (btn && sidebar) {
    btn.addEventListener("click", function () {
        if (isMobileLayout()) {
            if (sidebar.classList.contains("show")) { closeSidebar(); }
            else { openMobileSidebar(); }
        } else {
            const isCollapsed = sidebar.classList.contains("collapsed");
            sidebar.classList.toggle("collapsed");
            body.classList.toggle("sidebar-collapsed", !isCollapsed);
            if (overlay) { overlay.classList.remove("show"); }
        }
    });
    if (overlay) { overlay.addEventListener("click", closeSidebar); }
}

window.addEventListener("resize", function () {
    if (isMobileLayout()) {
        body.classList.remove("sidebar-collapsed");
        sidebar.classList.remove("collapsed");
        if (!sidebar.classList.contains("show")) {
            if (overlay) { overlay.classList.remove("show"); }
        }
    } else {
        body.classList.remove("sidebar-open-mobile");
        if (overlay) { overlay.classList.remove("show"); }
        sidebar.classList.remove("show");
    }
});

/* ===== Marcar enlace activo según la ruta actual ===== */
(function () {
    var path = window.location.pathname;
    var links = document.querySelectorAll('.nav-menu .nav-link');
    var activeHref = document.querySelector('.nav-menu .nav-link.active');
    if (activeHref) { activeHref.classList.remove('active'); }
    links.forEach(function (link) {
        var href = link.getAttribute('href') || '';
        if (href && path.indexOf(href) !== -1) { link.classList.add('active'); }
    });
})();
</script>

<!-- Modal de confirmación reutilizable -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i id="confirmIcono" class="fas fa-question-circle fa-3x text-primary"></i>
                </div>
                <h5 class="mb-1" id="confirmTitulo">Confirmar acción</h5>
                <p class="text-muted mb-0" id="confirmMensaje"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                <button type="button" class="btn btn-light" id="confirmCancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmAceptar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
window.confirmar = function (opciones) {
    return new Promise(function (resolve) {
        var opts = Object.assign({
            titulo: 'Confirmar acción', mensaje: '¿Desea continuar?',
            acepText: 'Confirmar', acepClase: 'btn-danger', cancelText: 'Cancelar',
            icono: 'fa-question-circle', iconoClase: 'text-primary'
        }, opciones || {});
        var modal = document.getElementById('modalConfirmacion');
        document.getElementById('confirmTitulo').textContent = opts.titulo;
        document.getElementById('confirmMensaje').textContent = opts.mensaje;
        document.getElementById('confirmIcono').className = 'fas ' + opts.icono + ' fa-3x ' + opts.iconoClase;
        var btnAceptar = document.getElementById('confirmAceptar');
        btnAceptar.innerHTML = opts.acepText;
        btnAceptar.className = 'btn ' + opts.acepClase;
        document.getElementById('confirmCancelar').innerHTML = opts.cancelText;
        var resuelto = false;
        function resolver(valor) { if (resuelto) return; resuelto = true; bootstrap.Modal.getOrCreateInstance(modal).hide(); resolve(valor); }
        btnAceptar.onclick = function () { resolver(true); };
        document.getElementById('confirmCancelar').onclick = function () { resolver(false); };
        modal.addEventListener('hidden.bs.modal', function handler() {
            modal.removeEventListener('hidden.bs.modal', handler);
            resolver(false);
        });
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });
};
</script>

@yield('scripts')

</body>
</html>
