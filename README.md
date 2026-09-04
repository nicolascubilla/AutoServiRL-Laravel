# AutoServiRL (réplica en Laravel)

Réplica 1:1 del sistema AutoServiRL (PHP MVC) construida con Laravel, que apunta a la
misma base PostgreSQL `AutoserviRL`, replicando pantallas, rutas y lógica de negocio.

## Requisitos

- PHP >= 8.2 (con extensión `pgsql`)
- Composer
- PostgreSQL con la base `AutoserviRL` (la misma del proyecto original)

## Instalación

1. Copiar `.env.example` a `.env` y ajustar:

   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=AutoserviRL
   DB_USERNAME=postgres
   DB_PASSWORD=123
   ```

2. Instalar dependencias:

   ```
   composer install
   ```

3. Generar clave:

   ```
   php artisan key:generate
   ```

> La base se reutiliza tal cual (no se corre `php artisan migrate` contra ella).
> `database/migrations` contiene una única migración **espejo e idempotente** que replica
> el esquema actual de la base con `Schema::hasTable()`; es segura de ejecutar si se
> quiere regenerar el esquema desde cero en otra base.

## Ejecución

```
php artisan serve
```

Abrir `http://localhost:8000` e iniciar sesión con un usuario de `usuarios`
(estado `'A'`), por ejemplo `nico` (contraseña de producción).

## History

- `/` Dashboard
- `/productos` y `/stock` — alta de productos, código de barra, stock mínimo, entradas/salidas
- `/caja`, `/caja/historial`, `/caja/detalle/{id}` — apertura, cierre e historial de caja
- `/ventas` (POS), `/ventas/historial`, `/ticket/{venta}` — ventas, ticket
- `/facturacion/{venta}`, `/factura/ver/{id}`, `/factura/imprimir/{id}`, `/factura/historial`,
  `/facturacion/config`, `/cotizacion` — facturación electrónica (RFC-style)
- `/informes`, `/informes/imprimir`, `/informes/exportar?tipo=ventas|productos` — informes y CSV
- `/cambiar-contrasena` — cambio de contraseña (usa `password_hash`/`password_verify`)

## Pruebas

Config por defecto (`phpunit.xml`) usa SQLite en memoria: correr solo el smoke test.

```
php artisan test
```

Para el test de flujo completo contra PostgreSQL real (se revierte todo con transacciones,
no deja datos):

```
php artisan test --configuration phpunit.pgsql.xml
```

El test verifica: alta de producto, stock, apertura de caja, venta POS, ticket,
facturación (incluida la no-doble-emisión), historiales, cierre de caja, informes,
exportación CSV y cambio de contraseña.