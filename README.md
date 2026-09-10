# Logística Maruchan

Sistema de gestión logística para Sopas Maruchan. Controla el flujo completo de pedidos desde **Ventas → Almacén → (preparación y embarques) → Transporte → Tienda**, con trazabilidad completa de cada movimiento y una consola de indicadores.

## Stack

- **Laravel 13.10.1** · PHP 8.3 · Composer 2.x
- **MySQL 8** (desarrollo) · SQLite en memoria (tests)
- **laravel/ui** con Bootstrap 5 y autenticación
- **barryvdh/laravel-dompdf** — generación de PDF (manual y reportes)
- **endroid/qr-code 6** — códigos QR SVG data URI
- **phpoffice/phpspreadsheet 5** — exportación a Excel (.xlsx) desde módulo de reportes
- **Chart.js 4** — gráficas del dashboard (vía Vite)

## Requisitos

- PHP ≥ 8.3, Composer, Node.js (para compilar assets) y un servidor MySQL.

## Instalación

```bash
# 1. Dependencias y assets
composer install
npm install && npm run build

# 2. Configuración
cp .env.example .env        # ajusta DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate

# 3. Base de datos (crea la BD y ejecuta migraciones + seeds)
php artisan migrate:fresh --seed

# 4. Servidor
php artisan serve
```

Accede a `http://127.0.0.1:8000`. La raíz redirige a `/login`.

## Credenciales demo

Todos los usuarios comparten la contraseña `password`.

| Rol | Email |
|---|---|
| Administrador | `admin@maruchan.test` |
| Ventas | `ventas@maruchan.test` |
| Almacén | `almacen@maruchan.test` |
| Logística | `logistica@maruchan.test` |
| Transporte | `transporte@maruchan.test` |
| Tienda | `tienda@maruchan.test` |

Cada usuario es redirigido al inicio de su área según su rol.

## Módulos implementados

- **Autenticación y roles** — middleware `role`, inicio por rol, botón "Cerrar sesión" siempre visible.
- **Dashboard (admin)** — indicadores KPI + 4 gráficas Chart.js (pedidos por estado, embarques por estado, pedidos por día últimos 14 días, incidencias por tipo) + **KPIs avanzados**: tiempos promedio por etapa del pedido (recepción, preparación, en almacén, tránsito, ciclo total) y rotación de inventario (salidas 30 días / stock y días de cobertura).
- **Catálogo (presentaciones)** — CRUD exclusivo de admin.
- **Tiendas** — CRUD admin con código, nombre, dirección, persona de contacto y horario de atención.
- **Almacenes y ubicaciones** — CRUD admin con gestión de zonas/pasillos.
- **Clientes** — CRUD admin con nombre, RFC, contacto, teléfono y correo; asociados a las tiendas.
- **Vehículos** — CRUD admin con código, económico, placa, marca, modelo y conductor; desactivación lógica.
- **Pedidos de venta** — folio único `PED-YYYYMMDD-####`, items por presentación, búsqueda y detalle con historial + código QR.
- **Recepción y preparación en almacén** — pedidos pendientes, captura de cantidades recibidas, incidencias (faltante/sobrante/daño), verificación → `RECIBIDO`, clasificación → `CLASIFICADO`, preparación → `PREPARADO`.
- **Embarques** — folio único `EMB-YYYYMMDD-####`, asignación de pedidos preparados a vehículo y conductor, carga → `CARGADO`.
- **Transporte (viajes)** — salida a ruta → `EN_TRANSITO`, registro de **incidencias en ruta** (percance, parada, retraso, nota) y llegada a tienda → `ENTREGADO`.
- **Recepción en tienda** — confirmación de entrega → `RECIBIDO_TIENDA`, cierre → `CERRADO` (cierra también el embarque).
- **Trazabilidad** — consulta por folio con línea de tiempo completa + **código QR** descargable desde el detalle.
- **Códigos QR** — generados con `endroid/qr-code` v6 (SVG data URI); disponibles en pedidos y en Trazabilidad.
- **Reportes exportables (admin)** — pedidos, embarques, incidencias y movimientos con filtros por fecha y estado/tipo, descarga **PDF** (DomPDF) y **Excel** (PhpSpreadsheet .xlsx).
- **Alertas por correo** — avisos automáticos a los administradores: pedido entró al almacén, embarque salió a ruta, entrega en tienda e incidencias en ruta.
- **Devoluciones (tienda → almacén)** — la tienda registra devoluciones de pedidos entregados (motivo, productos y cantidades con condición), con trazabilidad; el almacén confirma la recepción (estados: solicitada → recibida).
- **Manual de usuario PDF** — `php artisan manual:pdf` genera el manual completo en `public/docs/`.

## Flujo y estados de pedido

```
Ventas                       Almacén                              Logística            Transporte       Tienda
CREADO → CONFIRMADO → EN_ALMACEN → RECIBIDO → CLASIFICADO → PREPARADO
  → ASIGNADO_EMBARQUE → CARGADO → EN_TRANSITO → RECIBIDO_TIENDA → CERRADO
```

Estados del embarque: `PREPARADO → CARGADO → EN_TRANSITO → ENTREGADO → CERRADO` (se cierra cuando todos sus pedidos están cerrados).

Cada transición registra un **movimiento de trazabilidad** (fecha/hora, usuario, estado, detalle) sobre el pedido y/o el embarque.

## Tests

```bash
php artisan test
```

185 tests / 560 aserciones. Cubren esquema, modelos, roles, seeders, CRUDs (presentaciones, tiendas con contacto y horario, almacenes, vehículos y clientes), pedidos, recepción, clasificación, preparación, embarques, transporte, cierre en tienda, trazabilidad, dashboard con gráficas (Chart.js) y KPIs, códigos QR, **incidencias en ruta**, **reportes exportables** (PDF y Excel), **alertas por correo**, **devoluciones de tienda** y logout. Únicamente SQLite en memoria (sin tocar la BD de desarrollo).

## Estructura relevante

```
app/Models/               Modelos Eloquent (+ Order::recordMovement)
app/Http/Controllers/     Controladores por área funcional
app/Http/Middleware/      EnsureUserHasRole (alias: role)
app/Http/Requests/        Validaciones de formularios
app/Support/              Utilidades (OrderItemStock, Qr)
database/migrations/      19 migraciones del esquema
database/seeders/         Datos demo idempotentes
resources/views/          Vistas por módulo + partials reutilizables
resources/js/dashboard.js Gráficas Chart.js del dashboard (por Vite)
routes/web.php            Rutas + middlewares
tests/Feature/            Suites de tests por módulo
```

## Manual de usuario

El manual en PDF se genera con:

```bash
php artisan manual:pdf
```

El archivo queda en `public/docs/manual-usuario-logistica-maruchan.pdf` (vista: `resources/views/manual/pdf.blade.php`).

La **Fase 4** (reportes exportables, KPIs avanzados, alertas por correo y devoluciones) está completa.